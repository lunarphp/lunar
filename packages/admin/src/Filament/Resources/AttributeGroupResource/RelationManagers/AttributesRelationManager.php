<?php

namespace Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Language;

class AttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributes';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::attribute.plural_label');
    }

    protected static ?string $recordTitleAttribute = 'name.en';  // TODO: localise somehow

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TranslatedText::make('name')
                    ->label(
                        __('lunarpanel::attribute.form.name.label')
                    )
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                        if ($operation !== 'create') {
                            return;
                        }
                        $set('handle', Str::slug($state[Language::getDefault()->code])); // TODO : create new global variable on LunarPanelManager with default language ?
                    }),
                Forms\Components\TextInput::make('description.en') // TODO: localise
                    ->label(
                        __('lunarpanel::attribute.form.description.label')
                    )
                    ->helperText(
                        __('lunarpanel::attribute.form.description.helper')
                    )
                    ->maxLength(255),
                Forms\Components\TextInput::make('handle')
                    ->label(
                        __('lunarpanel::attribute.form.handle.label')
                    )->dehydrated()
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, RelationManager $livewire) {
                        return $rule->where('attribute_group_id', $livewire->ownerRecord->id);
                    })
                    ->required(),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Toggle::make('searchable')
                        ->label(
                            __('lunarpanel::attribute.form.searchable.label')
                        )->default(false),
                    Forms\Components\Toggle::make('filterable')
                        ->label(
                            __('lunarpanel::attribute.form.filterable.label')
                        )->default(false),
                    Forms\Components\Toggle::make('required')
                        ->label(
                            __('lunarpanel::attribute.form.required.label')
                        )->default(false),
                ]),
                Forms\Components\Select::make('type')->label(
                    __('lunarpanel::attribute.form.type.label')
                )->options(
                    AttributeData::getFieldTypes()->mapWithKeys(function ($fieldType) {
                        $langKey = strtolower(
                            class_basename($fieldType)
                        );

                        return [
                            $fieldType => __("lunarpanel::fieldtypes.{$langKey}.label"),
                        ];
                    })->toArray()
                )->required()->live()->afterStateUpdated(fn (Forms\Components\Select $component) => $component
                    ->getContainer()
                    ->getComponent('configuration')
                    ->getChildComponentContainer()
                    ->fill()),
                Forms\Components\TextInput::make('validation_rules')->label(
                    __('lunarpanel::attribute.form.validation_rules.label')
                )->string()->nullable(),
                Forms\Components\Grid::make(1)
                    ->schema(function (Forms\Get $get) {
                        return AttributeData::getConfigurationFields($get('type'));
                    })->key('configuration')->statePath('configuration'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TranslatedTextColumn::make('name')->label(
                    __('lunarpanel::attribute.table.name.label')
                ),
                Tables\Columns\TextColumn::make('description.en')->label(
                    __('lunarpanel::attribute.table.description.label')
                ),
                Tables\Columns\TextColumn::make('handle')
                    ->label(
                        __('lunarpanel::attribute.table.handle.label')
                    ),
                Tables\Columns\TextColumn::make('type')->label(
                    __('lunarpanel::attribute.table.type.label')
                ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data, RelationManager $livewire) {
                    $data['system'] = false;
                    $data['attribute_type'] = $livewire->ownerRecord->attributable_type;
                    $data['position'] = $livewire->ownerRecord->attributes()->count() + 1;

                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc')
            ->reorderable('position');
    }
}
