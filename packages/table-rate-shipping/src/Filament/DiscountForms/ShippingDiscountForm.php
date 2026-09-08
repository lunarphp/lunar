<?php

namespace Lunar\Shipping\Filament\DiscountForms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Lunar\Core\Facades\PriceCalculator;
use Lunar\Core\Models\Currency;
use Lunar\Filament\Contracts\DiscountFormType;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * The Filament form for ShippingDiscount, registered against the type from
 * ShippingPlugin so the type itself carries no Filament dependency.
 */
class ShippingDiscountForm implements DiscountFormType
{
    /**
     * Return the Filament form schema for the admin panel.
     */
    public function lunarPanelSchema(): array
    {
        $currencies = Currency::enabled()->get();

        $priceFields = $currencies->map(fn ($currency) => TextInput::make("prices.{$currency->code}")
            ->label($currency->name)
            ->helperText($currency->code)
            ->numeric()
            ->minValue(0)
            ->visible(fn (Get $get) => ($get('type') ?? 'fixed') === 'fixed')
        )->toArray();

        return [
            Repeater::make('data.methods')
                ->label(__('shipping::discounts.shipping_discount.form.methods.label'))
                ->addActionLabel(__('shipping::discounts.shipping_discount.form.methods.add_label'))
                ->schema([
                    Select::make('shipping_method_id')
                        ->label(__('shipping::discounts.shipping_discount.form.shipping_method_id.label'))
                        ->placeholder(__('shipping::discounts.shipping_discount.form.shipping_method_id.placeholder'))
                        ->options(fn () => ShippingMethod::get()->pluck('name', 'id'))
                        ->nullable(),
                    Select::make('type')
                        ->label(__('shipping::discounts.shipping_discount.form.type.label'))
                        ->options([
                            'fixed' => __('shipping::discounts.shipping_discount.form.type.options.fixed'),
                            'percentage' => __('shipping::discounts.shipping_discount.form.type.options.percentage'),
                        ])
                        ->default('fixed')
                        ->live()
                        ->required(),
                    TextInput::make('percentage')
                        ->label(__('shipping::discounts.shipping_discount.form.percentage.label'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->visible(fn (Get $get) => $get('type') === 'percentage'),
                    Group::make()
                        ->columnSpanFull()
                        ->columns(3)
                        ->schema($priceFields)
                        ->visible(fn (Get $get) => ($get('type') ?? 'fixed') === 'fixed'),
                ])
                ->columns(2),
        ];
    }

    /**
     * Mutate form data before filling (convert stored integer prices to decimals).
     */
    public function lunarPanelOnFill(array $data): array
    {
        $currencies = Currency::enabled()->get();

        foreach ($data['data']['methods'] ?? [] as $i => $method) {
            if (($method['type'] ?? 'fixed') !== 'fixed') {
                continue;
            }
            foreach ($currencies as $currency) {
                $stored = $method['prices'][$currency->code] ?? null;
                if ($stored !== null) {
                    $data['data']['methods'][$i]['prices'][$currency->code] = $stored / $currency->factor;
                }
            }
        }

        return $data;
    }

    /**
     * Mutate form data before saving (convert decimal prices to integers and handle min_prices).
     */
    public function lunarPanelOnSave(array $data): array
    {
        $currencies = Currency::enabled()->get();

        foreach ($currencies as $currency) {
            $minPrice = $data['data']['min_prices'][$currency->code] ?? null;
            if ($minPrice !== null) {
                $data['data']['min_prices'][$currency->code] = PriceCalculator::toMinor($minPrice, $currency);
            }
        }

        foreach ($data['data']['methods'] ?? [] as $i => $method) {
            if (($method['type'] ?? 'fixed') !== 'fixed') {
                continue;
            }
            foreach ($currencies as $currency) {
                $price = $method['prices'][$currency->code] ?? null;
                if ($price !== null) {
                    $data['data']['methods'][$i]['prices'][$currency->code] = PriceCalculator::toMinor($price, $currency);
                }
            }
        }

        return $data;
    }

    /**
     * No additional relation managers required.
     */
    public function lunarPanelRelationManagers(): array
    {
        return [];
    }
}
