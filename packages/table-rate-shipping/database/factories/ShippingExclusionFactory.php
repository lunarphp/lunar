<?php

namespace Lunar\Shipping\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Product;
use Lunar\Shipping\Models\ShippingExclusion;

class ShippingExclusionFactory extends Factory
{
    protected $model = ShippingExclusion::class;

    public function definition(): array
    {
        return [
            'purchasable_id' => 1,
            'purchasable_type' => Product::morphName(),
        ];
    }
}
