<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Lunar\Core\Contracts\Actions\Locations\CreatesLocation;
use Lunar\Panel\Http\Requests\Settings\LocationRequest;

class LocationCreateController
{
    public function store(LocationRequest $request, CreatesLocation $createsLocation): RedirectResponse
    {
        $createsLocation->execute($request->locationAttributes());

        return redirect()->route('panel.settings.locations.index')->with('success', __('panel::locations.flash_created'));
    }
}
