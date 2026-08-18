<?php

namespace Lunar\Core\Actions\TaxZones;

use Lunar\Core\Contracts\Actions\TaxZones\CreatesTaxZone;
use Lunar\Core\Models\TaxZone;

/**
 * Create a tax zone. The model's created hook keeps at most one zone marked
 * default.
 */
class CreateTaxZone implements CreatesTaxZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): TaxZone
    {
        return TaxZone::create($attributes);
    }
}
