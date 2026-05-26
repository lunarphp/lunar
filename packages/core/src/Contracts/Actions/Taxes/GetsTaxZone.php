<?php

namespace Lunar\Core\Contracts\Actions\Taxes;

use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\Contracts\TaxZone as TaxZoneContract;

interface GetsTaxZone
{
    public function execute(?Addressable $address = null): ?TaxZoneContract;
}
