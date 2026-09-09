<?php

namespace Lunar\Shipping\Checkout;

use Illuminate\Support\Collection;
use Lunar\Checkout\Contracts\DeliveryCountries;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Country;
use Lunar\Shipping\Models\ShippingZone;

/**
 * Derives the checkout's delivery countries from the shipping zones: a
 * country is deliverable when some zone covering it carries a rate for an
 * enabled shipping method. Country, state and postcode zones all name their
 * countries; an unrestricted zone with a live rate means everywhere.
 *
 * Bound over the checkout's default only when the checkout package is
 * installed, so this package stays usable without it.
 */
class ShippingZoneCountries implements DeliveryCountries
{
    public function available(Cart $cart): Collection
    {
        $zones = ShippingZone::query()
            ->whereHas('rates.shippingMethod', fn ($query) => $query->where('enabled', true))
            ->with('countries', 'states.country')
            ->get();

        if ($zones->contains(fn (ShippingZone $zone): bool => $zone->type === 'unrestricted')) {
            return Country::query()->orderBy('name')->get();
        }

        return $zones
            ->flatMap(fn (ShippingZone $zone): Collection => $zone->countries->merge(
                $zone->states->map(fn ($state) => $state->country)->filter(),
            ))
            ->unique('id')
            ->sortBy('name')
            ->values();
    }
}
