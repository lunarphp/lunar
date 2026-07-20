<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\TaxZones\CreatesTaxZone;
use Lunar\Panel\Http\Requests\Settings\TaxZoneRequest;

class TaxZoneCreateController
{
    public function store(TaxZoneRequest $request, CreatesTaxZone $createsTaxZone): RedirectResponse
    {
        $attributes = $request->taxZoneAttributes();

        // The zone's coverage and rates are managed on the edit screen.
        unset($attributes['countries'], $attributes['states'], $attributes['postcodes'], $attributes['customer_groups'], $attributes['rates']);

        $attributes['active'] ??= true;
        $attributes['default'] ??= false;

        $taxZone = $createsTaxZone->execute($attributes);

        return redirect()
            ->route('panel.settings.tax-zones.edit', $taxZone)
            ->with('success', __('panel::tax_zones.flash_created'));
    }
}
