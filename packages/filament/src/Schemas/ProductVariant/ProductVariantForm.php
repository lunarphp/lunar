<?php

namespace Lunar\Filament\Schemas\ProductVariant;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Lunar\Core\Contracts\FulfilmentMethod;
use Lunar\Core\Facades\Converter;
use Lunar\Core\Facades\FulfilmentMethods;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Filament\Forms\Components\Attributes;
use Lunar\Filament\Forms\Components\TextInputSelectAffix;
use Lunar\Filament\Support\Concerns\CallsHooks;

class ProductVariantForm
{
    use CallsHooks;

    public static function configure(Schema $schema): Schema
    {
        return self::callStaticLunarHook(
            'configureForm',
            $schema
                ->components([
                    static::getAttributeDataComponent(),
                ])
                ->columns(1),
        );
    }

    public static function getMainComponents(): array
    {
        return [
            static::getSkuComponent(),
        ];
    }

    public static function getAttributeDataComponent(): Component
    {
        return Attributes::make();
    }

    public static function getSkuComponent(): TextInput
    {
        return TextInput::make('sku');
    }

    public static function getGtinComponent(): TextInput
    {
        return TextInput::make('gtin')->label(
            __('lunar-filament::productvariant.form.gtin.label')
        );
    }

    public static function getMpnComponent(): TextInput
    {
        return TextInput::make('mpn')->label(
            __('lunar-filament::productvariant.form.mpn.label')
        );
    }

    public static function getEanComponent(): TextInput
    {
        return TextInput::make('ean')->label(
            __('lunar-filament::productvariant.form.ean.label')
        );
    }

    public static function getBackorderComponent(): TextInput
    {
        return TextInput::make('backorder')
            ->label(__('lunar-filament::productvariant.form.backorder.label'))
            ->hintIcon('heroicon-m-question-mark-circle')
            ->hintIconTooltip(__('lunar-filament::productvariant.form.backorder.tooltip'))
            ->numeric();
    }

    public static function getSellingPolicyComponent(): Select
    {
        return Select::make('selling_policy')
            ->options([
                'always' => __('lunar-filament::productvariant.form.selling_policy.options.always'),
                'in_stock' => __('lunar-filament::productvariant.form.selling_policy.options.in_stock'),
                'in_stock_or_on_backorder' => __('lunar-filament::productvariant.form.selling_policy.options.in_stock_or_on_backorder'),
            ])
            ->label(__('lunar-filament::productvariant.form.selling_policy.label'))
            ->hintIcon('heroicon-m-question-mark-circle')
            ->hintIconTooltip(__('lunar-filament::productvariant.form.selling_policy.tooltip'));
    }

    public static function getUnitQtyComponent(): TextInput
    {
        return TextInput::make('unit_quantity')
            ->label(__('lunar-filament::productvariant.form.unit_quantity.label'))
            ->hintIcon('heroicon-m-question-mark-circle')
            ->hintIconTooltip(__('lunar-filament::productvariant.form.unit_quantity.tooltip'))
            ->numeric()
            ->minValue(1);
    }

    public static function getQuantityIncrementComponent(): TextInput
    {
        return TextInput::make('quantity_increment')
            ->label(__('lunar-filament::productvariant.form.quantity_increment.label'))
            ->hintIcon('heroicon-m-question-mark-circle')
            ->hintIconTooltip(__('lunar-filament::productvariant.form.quantity_increment.tooltip'))
            ->numeric();
    }

    public static function getMinQuantityComponent(): TextInput
    {
        return TextInput::make('min_quantity')
            ->label(__('lunar-filament::productvariant.form.min_quantity.label'))
            ->hintIcon('heroicon-m-question-mark-circle')
            ->hintIconTooltip(__('lunar-filament::productvariant.form.min_quantity.tooltip'))
            ->numeric();
    }

    public static function getTaxClassIdComponent(): Select
    {
        return Select::make('tax_class_id')
            ->label(__('lunar-filament::productvariant.form.tax_class_id.label'))
            ->options(TaxClass::all()->pluck('name', 'id'))
            ->required();
    }

    public static function getTaxRefComponent(): TextInput
    {
        return TextInput::make('tax_ref')
            ->label(__('lunar-filament::product.pages.pricing.form.tax_ref.label'))
            ->helperText(__('lunar-filament::product.pages.pricing.form.tax_ref.tooltip'));
    }

    public static function getShippableComponent(): Toggle
    {
        return Toggle::make('shippable')
            ->label(__('lunar-filament::productvariant.form.shippable.label'))
            ->live()
            ->columnSpan(2);
    }

    /**
     * Every registered fulfilment method, ticked for the ones this variant
     * uses. A variant either ships or doesn't, so ticking a method of the
     * other kind replaces the ticked methods; the page sets `shippable` from
     * them with shipsWith().
     */
    public static function getFulfilmentMethodsComponent(): CheckboxList
    {
        return CheckboxList::make('fulfilment_methods')
            ->label(__('lunar-filament::productvariant.form.fulfilment_methods.label'))
            ->helperText(__('lunar-filament::productvariant.form.fulfilment_methods.helper_text'))
            ->options(fn (): array => static::fulfilmentMethodOptions())
            ->live()
            ->afterStateUpdated(fn (?array $state, ?array $old, Set $set) => $set(
                'fulfilment_methods',
                static::unmixedFulfilmentMethods($state ?? [], $old ?? []),
            ))
            ->columns(3)
            ->columnSpan(2);
    }

    /**
     * Every registered method, keyed by method key, with the methods that
     * fulfil a shippable variant first.
     *
     * @return array<string, string>
     */
    public static function fulfilmentMethodOptions(): array
    {
        $physical = static::physicalFulfilmentMethods();

        return FulfilmentMethods::all()
            ->sortBy(fn (FulfilmentMethod $method): bool => ! in_array($method->getKey(), $physical, true))
            ->map(fn (FulfilmentMethod $method): string => $method->getLabel())
            ->all();
    }

    /**
     * The keys of the methods that fulfil a shippable variant.
     *
     * @return list<string>
     */
    public static function physicalFulfilmentMethods(): array
    {
        return FulfilmentMethods::supporting(new ProductVariant(['shippable' => true]))->keys()->values()->all();
    }

    /**
     * The methods to show ticked. Null on a shippable variant allows every
     * physical method, so it shows them all; a variant that doesn't ship
     * shows only the methods it names.
     *
     * @param  list<string>|null  $stored
     * @return list<string>
     */
    public static function tickedFulfilmentMethods(?array $stored, bool $shippable): array
    {
        $pool = $shippable
            ? static::physicalFulfilmentMethods()
            : array_values(array_diff(array_keys(static::fulfilmentMethodOptions()), static::physicalFulfilmentMethods()));

        return $stored === null
            ? ($shippable ? $pool : [])
            : array_values(array_intersect($pool, $stored));
    }

    /**
     * Keep the ticked methods to one kind: when a newly ticked method is of
     * the other kind than the rest, only the methods of its kind stay.
     *
     * @param  array<int, string>  $ticked
     * @param  array<int, string>  $previous
     * @return list<string>
     */
    public static function unmixedFulfilmentMethods(array $ticked, array $previous): array
    {
        $physical = static::physicalFulfilmentMethods();
        $added = array_values(array_diff($ticked, $previous));

        if ($added === []) {
            return array_values($ticked);
        }

        $ships = in_array($added[0], $physical, true);

        return array_values(array_filter($ticked, fn (string $key): bool => in_array($key, $physical, true) === $ships));
    }

    /**
     * Whether the ticked methods make the variant shippable.
     *
     * @param  array<int, string>|null  $ticked
     */
    public static function shipsWith(?array $ticked): bool
    {
        return array_intersect(static::physicalFulfilmentMethods(), $ticked ?? []) !== [];
    }

    /**
     * The `fulfilment_methods` value to store for the ticked methods: null
     * when nothing is ticked, so there is nothing to fulfil, and when every
     * physical method is ticked, so a method registered later stays
     * available.
     *
     * @param  array<int, string>|null  $ticked
     * @return list<string>|null
     */
    public static function allowedFulfilmentMethods(?array $ticked): ?array
    {
        $physical = static::physicalFulfilmentMethods();
        $allowed = array_values(array_intersect(array_keys(static::fulfilmentMethodOptions()), $ticked ?? []));
        $ships = array_intersect($physical, $allowed) !== [];

        return $allowed === [] || ($ships && count($allowed) === count($physical)) ? null : $allowed;
    }

    public static function getMeasurements(?string $key = null): array
    {
        $measurements = Converter::getMeasurements();

        return collect(array_keys($measurements[$key] ?? []))
            ->mapWithKeys(fn ($value) => [$value => $value])
            ->toArray();
    }

    public static function getLengthComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('length_value')
            ->label(__('lunar-filament::productvariant.form.length_value.label'))
            ->numeric()
            ->select(
                fn () => Select::make('length_unit')
                    ->options(static::getMeasurements('length'))
                    ->label(__('lunar-filament::productvariant.form.length_unit.label'))
                    ->selectablePlaceholder(false)
            );
    }

    public static function getWidthComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('width_value')
            ->label(__('lunar-filament::productvariant.form.width_value.label'))
            ->numeric()
            ->select(
                fn () => Select::make('width_unit')
                    ->options(static::getMeasurements('length'))
                    ->label(__('lunar-filament::productvariant.form.width_unit.label'))
                    ->selectablePlaceholder(false)
            );
    }

    public static function getHeightComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('height_value')
            ->label(__('lunar-filament::productvariant.form.height_value.label'))
            ->numeric()
            ->select(
                fn () => Select::make('height_unit')
                    ->options(static::getMeasurements('length'))
                    ->label(__('lunar-filament::productvariant.form.height_unit.label'))
                    ->selectablePlaceholder(false)
            );
    }

    public static function getWeightComponent(): TextInputSelectAffix
    {
        return TextInputSelectAffix::make('weight_value')
            ->label(__('lunar-filament::productvariant.form.weight_value.label'))
            ->numeric()
            ->select(
                fn () => Select::make('weight_unit')
                    ->options(static::getMeasurements('weight'))
                    ->label(__('lunar-filament::productvariant.form.weight_unit.label'))
                    ->selectablePlaceholder(false)
            );
    }
}
