<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Regions\DeletesRegion;
use Lunar\Core\Contracts\Actions\Regions\UpdatesRegion;
use Lunar\Core\Exceptions\RegionActionException;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\Region;
use Lunar\Core\Models\TaxZone;
use Lunar\Panel\Http\Requests\Settings\RegionRequest;

class RegionEditController
{
    public function edit(Region $region): Response
    {
        return Inertia::render('settings/regions/Edit', [
            'region' => [
                'id' => $region->id,
                'name' => $region->name,
                'handle' => $region->handle,
                'channel_id' => $region->channel_id,
                'currency_id' => $region->currency_id,
                'language_id' => $region->language_id,
                'tax_zone_id' => $region->tax_zone_id,
                'prices_inc_tax' => $region->prices_inc_tax,
                'default' => $region->default,
                'countries' => $region->countries()->pluck('country_id'),
            ],
            'channels' => Channel::query()->orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::query()->orderBy('code')->get(['id', 'code', 'name']),
            'languages' => Language::query()->orderBy('code')->get(['id', 'code', 'name']),
            'taxZones' => TaxZone::query()->orderBy('name')->get(['id', 'name']),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name', 'iso2']),
            'hasOrderHistory' => Order::query()->where('region_id', $region->id)->exists(),
            'urls' => [
                'update' => route('panel.settings.regions.update', $region),
                'destroy' => route('panel.settings.regions.destroy', $region),
                'index' => route('panel.settings.regions.index'),
            ],
        ]);
    }

    public function update(RegionRequest $request, Region $region, UpdatesRegion $updatesRegion): RedirectResponse
    {
        try {
            $updatesRegion->execute($region, $request->regionAttributes());
        } catch (RegionActionException) {
            return back()->with('error', __('panel::regions.default_unset_blocked'));
        }

        return back()->with('success', __('panel::regions.flash_updated'));
    }

    public function destroy(Region $region, DeletesRegion $deletesRegion): RedirectResponse
    {
        try {
            $deletesRegion->execute($region);
        } catch (RegionActionException) {
            return back()->with('error', $region->default
                ? __('panel::regions.delete_blocked_default')
                : __('panel::regions.delete_blocked'));
        }

        return redirect()->route('panel.settings.regions.index')->with('success', __('panel::regions.flash_deleted'));
    }
}
