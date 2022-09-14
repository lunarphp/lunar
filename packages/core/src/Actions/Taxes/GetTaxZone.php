<?php

namespace Lunar\Actions\Taxes;

use Lunar\Base\Addressable;
use Lunar\Models\TaxZone;

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
