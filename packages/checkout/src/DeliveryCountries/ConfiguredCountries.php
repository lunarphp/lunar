<?php

namespace Lunar\Checkout\DeliveryCountries;

use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\DeliveryCountries;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;

/**
 * The default country source: `lunar.checkout.delivery_countries`, a list of
 * ISO 3166-1 alpha-2 codes, or every country Lunar knows when the list is
 * null. Configured order is kept so a store can lead with its home market.
 */
class ConfiguredCountries implements DeliveryCountries
{
    public function available(Cart $cart): Collection
    {
        /** @var list<string>|null $codes */
        $codes = config('lunar.checkout.delivery_countries');

        if ($codes === null) {
            return Country::query()->orderBy('name')->get();
        }

        $codes = array_values(array_map('strtoupper', $codes));

        return Country::query()
            ->whereIn('iso2', $codes)
            ->get()
            ->sortBy(fn (Country $country): int => (int) array_search($country->iso2, $codes, true))
            ->values();
    }
}
