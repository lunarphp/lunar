<?php

namespace Lunar\Core\Actions\Countries;

use Lunar\Core\Contracts\Actions\Countries\DeletesCountryState;
use Lunar\Core\Exceptions\CountryActionException;
use Lunar\Core\Models\State;
use Lunar\Core\Models\TaxZoneState;

/**
 * Delete a state. States referenced by a tax zone are kept — remove them from
 * the zone first — so zone coverage never silently shrinks.
 */
class DeleteCountryState implements DeletesCountryState
{
    public function execute(State $state): void
    {
        if (TaxZoneState::query()->where('state_id', $state->id)->exists()) {
            throw new CountryActionException('Cannot delete a state referenced by a tax zone.');
        }

        $state->delete();
    }
}
