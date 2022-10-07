<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\CustomerGroup;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCustomerGroup;

class TaxZoneCustomerGroupFactory extends Factory
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
