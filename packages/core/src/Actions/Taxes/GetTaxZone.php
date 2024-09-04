<?php

namespace Lunar\Actions\Taxes;

use Lunar\Base\Addressable;
use Lunar\Models\TaxZone;

class GetTaxZone
{
    public function execute(?Addressable $address = null)
    {
        if ($address && $address->postcode) {
            $postcodeZone = app(GetTaxZonePostcode::class)->execute($address->postcode);
            if ($postcodeZone) {
                return $postcodeZone->taxZone;
            }
        }

        if ($address && $address->state) {
            $stateZone = app(GetTaxZoneState::class)->execute($address->state);
            if ($stateZone) {
                return $stateZone->taxZone;
            }
        }

        if ($address && $address->country_id) {
            $countryZone = app(GetTaxZoneCountry::class)->execute($address->country_id);
            if ($countryZone) {
                return $countryZone->taxZone;
            }
        }

        return TaxZone::modelClass()::getDefault();
    }
}
