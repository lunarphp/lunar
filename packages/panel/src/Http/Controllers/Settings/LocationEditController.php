<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Locations\DeletesLocation;
use Lunar\Core\Contracts\Actions\Locations\UpdatesLocation;
use Lunar\Core\Exceptions\LocationActionException;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;
use Lunar\Panel\Http\Requests\Settings\LocationRequest;

class LocationEditController
{
    public function edit(Location $location): Response
    {
        return Inertia::render('settings/locations/Edit', [
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'handle' => $location->handle,
                'default' => $location->default,
            ],
            'hasFulfilments' => $location->fulfilments()->exists(),
            'hasStock' => StockLevel::query()->where('location_id', $location->id)->exists()
                || StockMovement::query()->where('location_id', $location->id)->exists(),
            'urls' => [
                'update' => route('panel.settings.locations.update', $location),
                'destroy' => route('panel.settings.locations.destroy', $location),
                'index' => route('panel.settings.locations.index'),
            ],
        ]);
    }

    public function update(LocationRequest $request, Location $location, UpdatesLocation $updatesLocation): RedirectResponse
    {
        try {
            $updatesLocation->execute($location, $request->locationAttributes());
        } catch (LocationActionException) {
            return back()->with('error', __('panel::locations.default_unset_blocked'));
        }

        return redirect()->route('panel.settings.locations.index')->with('success', __('panel::locations.flash_updated'));
    }

    public function destroy(Location $location, DeletesLocation $deletesLocation): RedirectResponse
    {
        try {
            $deletesLocation->execute($location);
        } catch (LocationActionException) {
            return back()->with('error', $location->default
                ? __('panel::locations.delete_blocked_default')
                : __('panel::locations.delete_blocked'));
        }

        return redirect()->route('panel.settings.locations.index')->with('success', __('panel::locations.flash_deleted'));
    }
}
