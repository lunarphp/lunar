<?php

namespace Lunar\Core\Actions\Taxes;

use Lunar\Core\Contracts\Actions\Taxes\GetsTaxZone;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\TaxZone;

class GetTaxZone implements GetsTaxZone
{
    public function __construct(
        protected GetTaxZonePostcode $getTaxZonePostcode,
        protected GetTaxZoneState $getTaxZoneState,
        protected GetTaxZoneCountry $getTaxZoneCountry,
    ) {}

    public function execute(?Addressable $address = null): ?TaxZone
    {
        if ($address && $address->postcode) {
            $postcodeZone = $this->getTaxZonePostcode->execute($address->postcode);
            if ($postcodeZone) {
                return $postcodeZone->taxZone;
            }
        }

        if ($address && $address->state) {
            $stateZone = $this->getTaxZoneState->execute($address->state, $address->country_id);
            if ($stateZone) {
                return $stateZone->taxZone;
            }
        }

        if ($address && $address->country_id) {
            $countryZone = $this->getTaxZoneCountry->execute($address->country_id);
            if ($countryZone) {
                return $countryZone->taxZone;
            }
        }

        return TaxZone::getDefault();
    }
}
