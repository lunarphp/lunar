<?php

namespace Lunar\Shipping\Panel\Support;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;

/**
 * Shapes a zone's rates for the edit screen: a summary for the table plus the
 * full editable payload (base prices and tiers in major units) so the
 * slideout can open on a row without another request.
 */
class RateRows
{
    public function __construct(protected PriceCalculatorInterface $priceCalculator) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forZone(ShippingZone $zone): array
    {
        $currencies = Currency::query()->get()->keyBy('id');
        $default = $currencies->firstWhere('default', true);

        return $zone->rates()
            ->with(['shippingMethod', 'prices'])
            ->get()
            ->sortBy(fn (ShippingRate $rate) => $rate->shippingMethod?->name)
            ->values()
            ->map(function (ShippingRate $rate) use ($currencies, $default, $zone): array {
                $chargeBy = $rate->shippingMethod?->data['charge_by'] ?? 'cart_total';

                $basePrices = $rate->prices
                    ->filter(fn (Price $price) => $price->min_quantity === 1 && $price->customer_group_id === null);

                $tiers = $rate->prices
                    ->filter(fn (Price $price) => $price->min_quantity > 1)
                    ->values();

                $defaultBase = $default ? $basePrices->firstWhere('currency_id', $default->id) : null;

                return [
                    'id' => $rate->id,
                    'shipping_method_id' => $rate->shipping_method_id,
                    'method_name' => $rate->shippingMethod?->name,
                    'enabled' => (bool) $rate->enabled,
                    'base_price' => $defaultBase && $default ? (new PriceValue((int) $defaultBase->price, $default))->format() : null,
                    'tiers_count' => $tiers->count(),
                    'base_prices' => $basePrices->mapWithKeys(function (Price $price) use ($currencies) {
                        $currency = $currencies->get($price->currency_id);

                        return $currency ? [$currency->code => $this->priceCalculator->toMajor((int) $price->price, $currency)] : [];
                    })->all(),
                    'tiers' => $tiers->map(function (Price $price) use ($currencies, $chargeBy) {
                        $currency = $currencies->get($price->currency_id);

                        return [
                            'customer_group_id' => $price->customer_group_id,
                            'currency_code' => $currency?->code,
                            'min_quantity' => $chargeBy === 'weight' || ! $currency
                                ? (int) $price->min_quantity
                                : $this->priceCalculator->toMajor((int) $price->min_quantity, $currency),
                            'price' => $currency ? $this->priceCalculator->toMajor((int) $price->price, $currency) : (int) $price->price,
                        ];
                    })->all(),
                    'urls' => [
                        'update' => route('panel.settings.shipping.zones.rates.update', [$zone, $rate]),
                        'destroy' => route('panel.settings.shipping.zones.rates.destroy', [$zone, $rate]),
                    ],
                ];
            })
            ->all();
    }
}
