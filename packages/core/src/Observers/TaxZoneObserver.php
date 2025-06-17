<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\TaxZone as TaxZoneContract;

class TaxZoneObserver
{
    /**
     * Handle the TazZone "deleting" event.
     *
     * @return void
     */
    public function deleting(TaxZoneContract $taxZone)
    {
        // Delete related data
        $taxZone->countries()->delete();
        $taxZone->states()->delete();
        $taxZone->postcodes()->delete();
        $taxZone->customerGroups()->delete();
        $taxZone->taxAmounts()->delete();
        $taxZone->taxRates()->delete();
    }
}
