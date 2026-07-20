<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Regions\CreatesRegion;
use Lunar\Panel\Http\Requests\Settings\RegionRequest;

class RegionCreateController
{
    public function store(RegionRequest $request, CreatesRegion $createsRegion): RedirectResponse
    {
        $region = $createsRegion->execute($request->regionAttributes());

        return redirect()
            ->route('panel.settings.regions.edit', $region)
            ->with('success', __('panel::regions.flash_created'));
    }
}
