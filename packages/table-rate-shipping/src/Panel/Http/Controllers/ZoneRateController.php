<?php

namespace Lunar\Shipping\Panel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Lunar\Shipping\Contracts\Actions\ShippingRates\DeletesShippingRate;
use Lunar\Shipping\Contracts\Actions\ShippingRates\SavesShippingRate;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Panel\Http\Requests\RateRequest;

/**
 * The zone's rates have their own endpoints: a rate carries per-currency base
 * prices and a tier list, which does not belong inside the zone's own form.
 */
class ZoneRateController
{
    public function store(RateRequest $request, ShippingZone $shippingZone, SavesShippingRate $savesShippingRate): RedirectResponse
    {
        $savesShippingRate->execute($shippingZone, null, $request->rateAttributes());

        return back()->with('success', __('shipping::zones.flash_rate_saved'));
    }

    public function update(RateRequest $request, ShippingZone $shippingZone, ShippingRate $shippingRate, SavesShippingRate $savesShippingRate): RedirectResponse
    {
        $this->assertBelongsToZone($shippingZone, $shippingRate);

        $savesShippingRate->execute($shippingZone, $shippingRate, $request->rateAttributes());

        return back()->with('success', __('shipping::zones.flash_rate_saved'));
    }

    public function destroy(ShippingZone $shippingZone, ShippingRate $shippingRate, DeletesShippingRate $deletesShippingRate): RedirectResponse
    {
        $this->assertBelongsToZone($shippingZone, $shippingRate);

        $deletesShippingRate->execute($shippingRate);

        return back()->with('success', __('shipping::zones.flash_rate_deleted'));
    }

    protected function assertBelongsToZone(ShippingZone $shippingZone, ShippingRate $shippingRate): void
    {
        abort_unless($shippingRate->shipping_zone_id === $shippingZone->id, 404);
    }
}
