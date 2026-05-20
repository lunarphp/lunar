<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\TaxZone;
use Lunar\Core\Models\TaxZoneCustomerGroup;

class TaxZoneCustomerGroupFactory extends BaseFactory
{
    protected $model = TaxZoneCustomerGroup::class;

    public function definition(): array
    {
        return [
            'customer_group_id' => CustomerGroup::factory(),
            'tax_zone_id' => TaxZone::factory(),
        ];
    }
}
