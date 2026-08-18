<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Core\Contracts\Actions\Countries\DeletesCountry;
use Lunar\Core\Contracts\Actions\Countries\UpdatesCountry;
use Lunar\Core\Exceptions\CountryActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZoneState;
use Lunar\Panel\Http\Requests\Settings\CountryRequest;

class CountryEditController
{
    public function edit(Country $country): Response
    {
        $zonedStateIds = TaxZoneState::query()
            ->whereIn('state_id', $country->states()->select('id'))
            ->pluck('state_id');

        return Inertia::render('settings/countries/Edit', [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'iso2' => $country->iso2,
                'iso3' => $country->iso3,
                'phonecode' => $country->phonecode,
                'emoji' => $country->emoji,
            ],
            'states' => $country->states()
                ->orderBy('name')
                ->get()
                ->map(fn (State $state): array => [
                    'id' => $state->id,
                    'name' => $state->name,
                    'code' => $state->code,
                    // States covered by a tax zone cannot be removed.
                    'inTaxZone' => $zonedStateIds->contains($state->id),
                    'urls' => [
                        'destroy' => route('panel.settings.countries.states.destroy', [$country, $state]),
                    ],
                ]),
            'urls' => [
                'update' => route('panel.settings.countries.update', $country),
                'destroy' => route('panel.settings.countries.destroy', $country),
                'index' => route('panel.settings.countries.index'),
                'storeState' => route('panel.settings.countries.states.store', $country),
            ],
        ]);
    }

    public function update(CountryRequest $request, Country $country, UpdatesCountry $updatesCountry): RedirectResponse
    {
        $updatesCountry->execute($country, $request->countryAttributes());

        return redirect()->route('panel.settings.countries.index')->with('success', __('panel::countries.flash_updated'));
    }

    public function destroy(Country $country, DeletesCountry $deletesCountry): RedirectResponse
    {
        try {
            $deletesCountry->execute($country);
        } catch (CountryActionException) {
            return back()->with('error', __('panel::countries.delete_blocked'));
        }

        return redirect()->route('panel.settings.countries.index')->with('success', __('panel::countries.flash_deleted'));
    }
}
