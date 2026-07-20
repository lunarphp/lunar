<?php

namespace Lunar\Core\Actions\TaxZones;

use Lunar\Core\Contracts\Actions\TaxZones\DeletesTaxZone;
use Lunar\Core\Exceptions\TaxZoneActionException;
use Lunar\Core\Models\TaxZone;

/**
 * Delete a tax zone. The default zone is kept — make another zone the
 * default first. The model's deleting hook removes the zone's coverage and
 * rates with it.
 */
class DeleteTaxZone implements DeletesTaxZone
{
    public function execute(TaxZone $taxZone): void
    {
        if ($taxZone->default) {
            throw new TaxZoneActionException('Cannot delete the default tax zone. Make another tax zone the default first.');
        }

        $taxZone->delete();
    }
}
