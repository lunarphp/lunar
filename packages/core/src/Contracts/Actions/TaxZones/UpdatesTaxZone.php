<?php

namespace Lunar\Core\Contracts\Actions\TaxZones;

use Lunar\Core\Models\TaxZone;

interface UpdatesTaxZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(TaxZone $taxZone, array $attributes): TaxZone;
}
