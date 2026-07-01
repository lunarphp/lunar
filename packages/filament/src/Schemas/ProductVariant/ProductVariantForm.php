<?php

namespace Lunar\Filament\Schemas\ProductVariant;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Lunar\Core\Facades\Converter;
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
            ->numeric();
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
            ->columnSpan(2);
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
