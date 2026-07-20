<?php

namespace Lunar\Core\Contracts\Actions\TaxZones;

use Lunar\Core\Models\TaxZone;

interface CreatesTaxZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): TaxZone;
}
