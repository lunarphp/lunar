<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Lunar\Shipping\Contracts\Actions\ShippingZones\CreatesShippingZone;
use Lunar\Shipping\Panel\Http\Requests\ZoneRequest;

class ZoneCreateController
{
    public function store(ZoneRequest $request, CreatesShippingZone $createsShippingZone): RedirectResponse
    {
        $attributes = $request->zoneAttributes();

        // Coverage, rates and exclusion lists are managed on the edit screen.
        unset($attributes['countries'], $attributes['states'], $attributes['postcodes'], $attributes['exclusion_lists']);

        $zone = $createsShippingZone->execute($attributes);

        return redirect()
            ->route('panel.settings.shipping.zones.edit', $zone)
            ->with('success', __('shipping::zones.flash_created'));
    }
}
