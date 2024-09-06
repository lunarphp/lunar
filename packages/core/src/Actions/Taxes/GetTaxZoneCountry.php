<?php

namespace Lunar\Actions\Taxes;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\TaxZoneCountry;

class GetTaxZoneCountry
{
    public function execute($countryId)
    {
        $taxZone = $this->getZone($countryId);

        if ($taxZone instanceof TaxZoneCountry) {
            return $taxZone;
        }

        if (! $taxZone) {
            return null;
        }
    }

    /**
     * Return the zone or zones which match this country.
     *
     * @return TaxZoneCountry|null
     */
    protected function getZone(int $countryId)
    {
        return TaxZoneCountry::whereHas(
            'taxZone',
            fn (Builder $query) => $query->where('active', true)
        )->whereCountryId($countryId)->first();
    }
}
