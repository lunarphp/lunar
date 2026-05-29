<?php

namespace Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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
use Illuminate\Validation\Rules\Unique;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\RelationManagers\BaseRelationManager;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Language;

class AttributesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'attributes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::attribute.plural_label');
    }

    protected static ?string $recordTitleAttribute = 'name.en';  // TODO: localise somehow

    public function getDefaultForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslatedText::make('name')
                    ->label(
                        __('lunarpanel::attribute.form.name.label')
                    )
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                        if ($operation !== 'create') {
                            return;
                        }
                        $set('handle', Str::slug($state[Language::getDefault()->code]));
                    }),
                TranslatedText::make('description')
                    ->label(
                        __('lunarpanel::attribute.form.description.label')
                    )
                    ->helperText(
                        __('lunarpanel::attribute.form.description.helper')
                    )
                    ->afterStateHydrated(fn ($state, $component) => $state ?: $component->state([Language::getDefault()->code => null]))
                    ->maxLength(255),
                TextInput::make('handle')
                    ->label(
                        __('lunarpanel::attribute.form.handle.label')
                    )->dehydrated()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Set $set) {
                        if ($operation !== 'create') {
                            return;
                        }

                        $set('handle', Str::snake(Str::lower($state)));
                    })
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, RelationManager $livewire) {
                        return $rule->where('attribute_type', $livewire->ownerRecord->attributable_type);
                    })->disabled(
                        fn (?Model $record) => (bool) $record
                    )
                    ->required(),
                Grid::make(3)->schema([
                    Toggle::make('searchable')
                        ->label(
                            __('lunarpanel::attribute.form.searchable.label')
                        )->default(false),
                    Toggle::make('filterable')
                        ->label(
                            __('lunarpanel::attribute.form.filterable.label')
                        )->default(false),
                    Toggle::make('required')
                        ->label(
                            __('lunarpanel::attribute.form.required.label')
                        )->default(false),
                ]),
                Select::make('type')->label(
                    __('lunarpanel::attribute.form.type.label')
                )->disabled(
                    fn (?Model $record) => (bool) $record
                )->options(
                    AttributeData::getFieldTypes()->mapWithKeys(function ($fieldType) {
                        $langKey = strtolower(
                            class_basename($fieldType)
                        );

                        return [
                            $fieldType => __("lunarpanel::fieldtypes.{$langKey}.label"),
                        ];
                    })->toArray()
                )->required()->live()->afterStateUpdated(fn (Select $component) => $component
                    ->getContainer()
                    ->getComponent('configuration')
                    ->getChildComponentContainer()

                    ->fill()),
                TextInput::make('validation_rules')->label(
                    __('lunarpanel::attribute.form.validation_rules.label')
                )
                    ->string()
                    ->nullable()
                    ->helperText(
                        __('lunarpanel::attribute.form.validation_rules.helper')
                    ),
                TextInput::make('default_value')->label(
                    __('lunarpanel::attribute.form.default_value.label')
                )
                    ->string()
                    ->nullable()
                    ->helperText(
                        __('lunarpanel::attribute.form.default_value.helper')
                    ),
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
                TranslatedTextColumn::make('name')->label(
                    __('lunarpanel::attribute.table.name.label')
                ),
                TextColumn::make('description.en')->label(
                    __('lunarpanel::attribute.table.description.label')
                ),
                TextColumn::make('handle')
                    ->label(
                        __('lunarpanel::attribute.table.handle.label')
                    ),
                TextColumn::make('type')->label(
                    __('lunarpanel::attribute.table.type.label')
                ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data, RelationManager $livewire) {
                    $data['configuration'] = $data['configuration'] ?? [];
                    $data['system'] = false;
                    $data['attribute_type'] = $livewire->ownerRecord->attributable_type;
                    $data['position'] = $livewire->ownerRecord->attributes()->count() + 1;

                    return $data;
                }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['configuration'] = AttributeData::mutateConfigurationForForm(
                            $data['type'] ?? null,
                            $data['configuration'] ?? [],
                        );

                        return $data;
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
