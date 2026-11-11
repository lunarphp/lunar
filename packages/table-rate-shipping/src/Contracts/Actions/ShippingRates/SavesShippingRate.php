<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingRates;

use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;

interface SavesShippingRate
{
    /**
     * Create a rate on the zone, or update the given one, replacing its base
     * prices and tiers with the supplied set.
     *
     * @param  array{
     *   shipping_method_id: int,
     *   enabled?: bool,
     *   base_prices?: array<string, int|float|string|null>,
     *   tiers?: array<int, array{customer_group_id?: int|null, currency_code: string, min_quantity: int|float|string, price: int|float|string}>
     * }  $attributes
     */
    public function execute(ShippingZone $shippingZone, ?ShippingRate $shippingRate, array $attributes): ShippingRate;
}
