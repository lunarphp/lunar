<?php

namespace Lunar\Actions\Taxes;

use Lunar\Models\TaxZoneState;

class GetTaxZoneState
{
    /**
     * Execute the action.
     *
     * @param  string  $state
     * @param  null|int  $countryId
     * @return null|TaxZoneState
     */
    public function execute($state, $countryId = null)
    {
        $stateZone = $this->getZoneMatches($state, $countryId);

        if ($stateZone instanceof TaxZoneState) {
            return $stateZone;
        }

        return null;
    }

    /**
     * Return the zone or zones which match the given state name/code.
     *
     * @param  string  $state
     * @param  null|int  $countryId
     * @return null|TaxZoneState
     */
    protected function getZoneMatches($state, $countryId = null)
    {
        $state = (string) $state;

        $stateZone = TaxZoneState::whereHas('state', function ($query) use ($state, $countryId) {
            $query->where(function ($query) use ($state) {
                return $query
                    ->where('name', $state)
                    ->orWhere('code', $state);
            });

            if ($countryId) {
                $query->where(function ($query) use ($countryId) {
                    return $query
                        ->whereNull('country_id')
                        ->orWhere('country_id', $countryId);
                });
            }

            return $query;
        })->whereHas('taxZone', function ($query) {
            return $query->where('active', true);
        })->first();

        if ($stateZone) {
            return $stateZone;
        }

        return null;
    }
}
