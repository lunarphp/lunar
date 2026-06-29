<?php

namespace Lunar\Core\Contracts\Actions\Taxes;

use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\TaxZone;

interface GetsTaxZone
{
    public function execute(?Addressable $address = null): ?TaxZone;
}
