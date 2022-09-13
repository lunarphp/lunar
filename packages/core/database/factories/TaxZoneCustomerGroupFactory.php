<?php

namespace Lunar\Database\Factories;

use Lunar\Models\CustomerGroup;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxZoneCustomerGroupFactory extends Factory
{
    protected $model = TaxZoneCustomerGroup::class;

    public function definition(): array
    {
        return [
            'customer_group_id' => CustomerGroup::factory(),
            'tax_zone_id'       => TaxZone::factory(),
        ];
    }
}
