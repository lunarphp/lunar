<?php

namespace Lunar\Shipping\Actions\ShippingRates;

use Illuminate\Support\Collection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\PriceCalculatorInterface;
use Lunar\Shipping\Contracts\Actions\ShippingRates\SavesShippingRate;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;

/**
 * Create or update a shipping rate on a zone, replacing its base prices and
 * tiers.
 *
 * Money arrives in major units keyed by currency code and is scaled through
 * the currency's own decimal places. A tier's `min_quantity` is a minimum
 * spend when the method charges by cart total (scaled the same way) or a raw
 * weight in the method's weight unit when it charges by weight.
 */
class SaveShippingRate implements SavesShippingRate
{
    public function __construct(protected PriceCalculatorInterface $priceCalculator) {}

    /**
     * {@inheritdoc}
     */
    public function execute(ShippingZone $shippingZone, ?ShippingRate $shippingRate, array $attributes): ShippingRate
    {
        $basePrices = $attributes['base_prices'] ?? null;
        $tiers = $attributes['tiers'] ?? null;

        unset($attributes['base_prices'], $attributes['tiers']);

        return DB::transaction(function () use ($shippingZone, $shippingRate, $attributes, $basePrices, $tiers): ShippingRate {
            if ($shippingRate) {
                $shippingRate->update($attributes);
            } else {
                $shippingRate = $shippingZone->rates()->create($attributes);
            }

            $shippingRate->load('shippingMethod');

            $currencies = $this->currencies();

            if ($basePrices !== null) {
                $shippingRate->basePrices()->delete();

                foreach ($basePrices as $code => $amount) {
                    $currency = $currencies->get($code);

                    if (! $currency || blank($amount)) {
                        continue;
                    }

                    $shippingRate->prices()->create([
                        'price' => $this->priceCalculator->toMinor($amount, $currency),
                        'currency_id' => $currency->id,
                        'customer_group_id' => null,
                        'min_quantity' => 1,
                    ]);
                }
            }

            if ($tiers !== null) {
                $shippingRate->priceBreaks()->delete();

                $chargeBy = $shippingRate->shippingMethod?->data['charge_by'] ?? 'cart_total';

                foreach ($tiers as $tier) {
                    $currency = $currencies->get($tier['currency_code']);

                    if (! $currency) {
                        continue;
                    }

                    $shippingRate->prices()->create([
                        'customer_group_id' => $tier['customer_group_id'] ?? null,
                        'currency_id' => $currency->id,
                        'price' => $this->priceCalculator->toMinor($tier['price'], $currency),
                        'min_quantity' => $chargeBy === 'weight'
                            ? (int) $tier['min_quantity']
                            : $this->priceCalculator->toMinor($tier['min_quantity'], $currency),
                    ]);
                }
            }

            return $shippingRate->unsetRelation('prices');
        });
    }

    /** @return Collection<string, Currency> */
    protected function currencies(): Collection
    {
        return Currency::query()->get()->keyBy('code');
    }
}
