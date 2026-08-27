<?php

namespace Lunar\Filament\Schemas\Attribute;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lunar\Core\Facades\AttributeManifest;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Rules\ValidRuleString;
use Lunar\Filament\Support\Concerns\CallsHooks;
use Lunar\Filament\Support\Facades\AttributeData;

/**
 * Form schema for creating and editing attributes (spec 0063). Composed in
 * full by the admin's AttributeResource; the attribute group relation manager
 * reuses the granular helpers minus the group select (the owner record
 * supplies the group).
 */
class AttributeForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema->components([
                Section::make()->schema(static::getMainComponents()),
            ])->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getNameComponent(),
            static::getHandleComponent(),
            static::getGroupComponent(),
            static::getModelTypesComponent(),
            static::getFlagsComponent(),
            static::getValidationRulesComponent(),
            static::getTypeComponent(),
            static::getConfigurationComponent(),
        ];
    }

    public static function getNameComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('lunar-filament::attribute.form.name.label'))
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                if ($operation !== 'create') {
                    return;
                }
                $set('handle', Str::slug($state, '_'));
            });
    }

    public static function getHandleComponent(): Component
    {
        return TextInput::make('handle')
            ->label(__('lunar-filament::attribute.form.handle.label'))
            ->dehydrated()
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
            ->required();
    }

    public static function getGroupComponent(): Component
    {
        return Select::make('attribute_group_id')
            ->label(__('lunar-filament::attribute.form.attribute_group.label'))
            ->placeholder(__('lunar-filament::attribute.form.attribute_group.placeholder'))
            ->options(
                fn () => AttributeGroup::query()->orderBy('position')->pluck('name', 'id')->all()
            );
    }

    public static function getModelTypesComponent(): Component
    {
        return Select::make('model_types')
            ->label(__('lunar-filament::attribute.form.model_types.label'))
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
            ->required();
    }

    public static function getFlagsComponent(): Component
    {
        return Grid::make(3)->schema([
            Toggle::make('searchable')
                ->label(__('lunar-filament::attribute.form.searchable.label'))
                ->default(false),
            Toggle::make('filterable')
                ->label(__('lunar-filament::attribute.form.filterable.label'))
                ->default(false),
            Toggle::make('required')
                ->label(__('lunar-filament::attribute.form.required.label'))
                ->default(false),
        ]);
    }

    public static function getValidationRulesComponent(): Component
    {
        return TagsInput::make('validation_rules')
            ->label(__('lunar-filament::attribute.form.validation_rules.label'))
            ->helperText(__('lunar-filament::attribute.form.validation_rules.helper'))
            ->placeholder('min:1')
            ->nestedRecursiveRules([new ValidRuleString]);
    }

    public static function getTypeComponent(): Component
    {
        return Select::make('type')
            ->label(__('lunar-filament::attribute.form.type.label'))
            ->disabled(
                fn (?Model $record) => (bool) $record
            )
            ->options(
                AttributeData::getFieldTypes()->mapWithKeys(function ($type) {
                    return [
                        $type => __("lunar-filament::fieldtypes.{$type}.label"),
                    ];
                })->sort()->toArray()
            )
            ->required()
            ->live()
            ->afterStateUpdated(fn (Select $component) => $component
                ->getContainer()
                ->getComponent('configuration')
                ->getChildComponentContainer()
                ->fill());
    }

    public static function getConfigurationComponent(): Component
    {
        return Grid::make(1)
            ->schema(function (Get $get) {
                return AttributeData::getConfigurationFields($get('type'));
            })
            ->key('configuration')
            ->statePath('configuration');
    }

    /**
     * Prepare a record's raw data for the form: mutate the stored
     * configuration into its form shape and fill the model_types selection.
     * Shared by the edit page and the relation manager's edit action so both
     * hydrate identically.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateDataForFill(Attribute $attribute, array $data): array
    {
        $data['configuration'] = AttributeData::mutateConfigurationForForm(
            $data['type'] ?? null,
            $data['configuration'] ?? [],
        );

        $data['model_types'] = $attribute->models()->pluck('model_type')->all();

        return $data;
    }
}
