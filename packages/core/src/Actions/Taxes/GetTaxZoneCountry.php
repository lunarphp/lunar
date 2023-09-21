<?php

namespace Lunar\Actions\Taxes;

use Lunar\Models\TaxZoneCountry;

class GetTaxZoneCountry
{
    public function execute($countryId)
    {
        $taxZone = $this->getZone($countryId);

        if ($taxZone instanceof TaxZoneCountry) {
            return $taxZone;
        }

        if (!$taxZone) {
            return null;
        }
    }

    /**
     * Return the zone or zones which match this country.
     *
     * @param int $countryId
     * @return TaxZoneCountry|null
     */
    protected function getZone(int $countryId)
    {
        return TaxZoneCountry::whereCountryId($countryId)->first();
    }
}
