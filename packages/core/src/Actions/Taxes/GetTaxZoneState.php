<?php

namespace Lunar\Actions\Taxes;

use Lunar\Models\TaxZoneState;

class GetTaxZoneState
{
    /**
     * Execute the action.
     *
     * @param  string  $state
     * @return null|TaxZoneState
     */
    public function execute($state)
    {
        $stateZone = $this->getZoneMatches($state);

        if ($stateZone instanceof TaxZoneState) {
            return $stateZone;
        }

        return null;
    }

    /**
     * Return the zone or zones which match the given state name/code.
     *
     * @param  string  $state
     * @return null|TaxZoneState
     */
    protected function getZoneMatches($state)
    {
        $state = (string) $state;

        $stateZone = TaxZoneState::whereHas('state', function ($query) use ($state) {
            return $query
                ->where('name', $state)
                ->orWhere('code', $state);
        })->first();

        if ($stateZone) {
            return $stateZone;
        }

        return null;
    }
}
