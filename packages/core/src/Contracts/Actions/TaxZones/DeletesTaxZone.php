<?php

namespace Lunar\Core\Contracts\Actions\TaxZones;

use Lunar\Core\Models\TaxZone;

interface DeletesTaxZone
{
    public function execute(TaxZone $taxZone): void;
}
