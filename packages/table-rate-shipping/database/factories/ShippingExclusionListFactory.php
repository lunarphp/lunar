<?php

namespace Lunar\Shipping\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Shipping\Models\ShippingExclusionList;

class ShippingExclusionListFactory extends Factory
{
    protected $model = ShippingExclusionList::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
