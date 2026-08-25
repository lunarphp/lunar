<?php

namespace Lunar\Filament\RelationManagers\AttributeGroup;

use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Core\Facades\AttributeManifest;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Filament\RelationManagers\BaseRelationManager;
use Lunar\Filament\Support\Facades\AttributeData;

class AttributesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'attributes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunar-filament::attribute.plural_label');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(
                        __('lunar-filament::attribute.form.name.label')
                    )
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                        if ($operation !== 'create') {
                            return;
                        }
                        $set('handle', Str::slug($state, '_'));
                    }),
                TextInput::make('handle')
                    ->label(
                        __('lunar-filament::attribute.form.handle.label')
                    )->dehydrated()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                        if ($operation !== 'create') {
                            return;
                        }

                        $set('handle', Str::snake(Str::lower($state)));
                    })
                    ->unique(ignoreRecord: true)
                    ->disabled(
                        fn (?Model $record) => (bool) $record
                    )
                    ->required(),
                Select::make('model_types')
                    ->label(
                        __('lunar-filament::attribute.form.model_types.label')
                    )
                    ->multiple()
                    ->options(
                        AttributeManifest::getTypes()->mapWithKeys(
                            fn ($type) => [ModelManifest::getMorphMapKey($type) => class_basename($type)]
                        )->toArray()
                    )
                    ->rules([
                        fn () => function (string $attribute, $value, Closure $fail): void {
                            $values = (array) $value;

                            if (in_array(Product::morphName(), $values, true)
                                && in_array(ProductVariant::morphName(), $values, true)) {
                                $fail(__('lunar-filament::attribute.form.model_types.product_and_variant_invalid'));
                            }
                        },
                    ])
                    ->required(),
                Grid::make(3)->schema([
                    Toggle::make('searchable')
                        ->label(
                            __('lunar-filament::attribute.form.searchable.label')
                        )->default(false),
                    Toggle::make('filterable')
                        ->label(
                            __('lunar-filament::attribute.form.filterable.label')
                        )->default(false),
                    Toggle::make('required')
                        ->label(
                            __('lunar-filament::attribute.form.required.label')
                        )->default(false),
                ]),
                TagsInput::make('validation_rules')
                    ->label(
                        __('lunar-filament::attribute.form.validation_rules.label')
                    )->helperText(
                        __('lunar-filament::attribute.form.validation_rules.helper')
                    )->placeholder('min:1'),
                Select::make('type')->label(
                    __('lunar-filament::attribute.form.type.label')
                )->disabled(
                    fn (?Model $record) => (bool) $record
                )->options(
                    AttributeData::getFieldTypes()->mapWithKeys(function ($type) {
                        return [
                            $type => __("lunar-filament::fieldtypes.{$type}.label"),
                        ];
                    })->sort()->toArray()
                )->required()->live()->afterStateUpdated(fn (Select $component) => $component
                    ->getContainer()
                    ->getComponent('configuration')
                    ->getChildComponentContainer()

                    ->fill()),
                Grid::make(1)
                    ->schema(function (Get $get) {
                        return AttributeData::getConfigurationFields($get('type'));
                    })
                    ->key('configuration')
                    ->statePath('configuration'),
            ]);
    }

    public function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(
                    __('lunar-filament::attribute.table.name.label')
                ),
                TextColumn::make('handle')
                    ->label(
                        __('lunar-filament::attribute.table.handle.label')
                    ),
                TextColumn::make('type')->label(
                    __('lunar-filament::attribute.table.type.label')
                ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->using(function (array $data, RelationManager $livewire) {
                    $modelTypes = (array) ($data['model_types'] ?? []);
                    unset($data['model_types']);

                    $data['configuration'] = $data['configuration'] ?? [];
                    $data['system'] = false;
                    $data['position'] = $livewire->ownerRecord->attributes()->count() + 1;

                    $attribute = $livewire->ownerRecord->attributes()->create($data);

                    foreach (array_unique($modelTypes) as $modelType) {
                        $attribute->models()->create(['model_type' => $modelType]);
                    }

                    return $attribute;
                }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Model $record): array {
                        $data['configuration'] = AttributeData::mutateConfigurationForForm(
                            $data['type'] ?? null,
                            $data['configuration'] ?? [],
                        );

                        $data['model_types'] = $record->models()->pluck('model_type')->all();

                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        $modelTypes = array_key_exists('model_types', $data)
                            ? array_unique((array) $data['model_types'])
                            : null;
                        unset($data['model_types']);

                        $record->update($data);

                        if ($modelTypes !== null) {
                            $record->models()->delete();

                            foreach ($modelTypes as $modelType) {
                                $record->models()->create(['model_type' => $modelType]);
                            }
                        }

                        return $record;
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }
}
