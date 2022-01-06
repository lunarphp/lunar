<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\CustomerGroup;
use GetCandy\Models\TaxZone;
use GetCandy\Models\TaxZoneCustomerGroup;
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
