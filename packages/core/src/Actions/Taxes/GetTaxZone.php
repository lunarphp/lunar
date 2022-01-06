<?php

namespace GetCandy\Actions\Taxes;

use GetCandy\Base\Addressable;
use GetCandy\Models\TaxZone;

class GetTaxZone
{
    public function execute(Addressable $address = null)
    {
        if ($address && $address->postcode) {
            $postcodeZone = app(GetTaxZonePostcode::class)->execute($address->postcode);
            if ($postcodeZone) {
                return $postcodeZone->taxZone;
            }
        }

        return TaxZone::getDefault();
    }
}
