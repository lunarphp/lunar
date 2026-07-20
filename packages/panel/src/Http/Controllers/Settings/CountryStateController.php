<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Lunar\Core\Contracts\Actions\Countries\CreatesCountryState;
use Lunar\Core\Contracts\Actions\Countries\DeletesCountryState;
use Lunar\Core\Exceptions\CountryActionException;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\State;

class CountryStateController
{
    public function store(Request $request, Country $country, CreatesCountryState $createsCountryState): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
        ]);

        $createsCountryState->execute($country, $validated);

        return back()->with('success', __('panel::countries.flash_state_created'));
    }

    public function destroy(Country $country, State $state, DeletesCountryState $deletesCountryState): RedirectResponse
    {
        try {
            $deletesCountryState->execute($state);
        } catch (CountryActionException) {
            return back()->with('error', __('panel::countries.state_delete_blocked'));
        }

        return back()->with('success', __('panel::countries.flash_state_deleted'));
    }
}
