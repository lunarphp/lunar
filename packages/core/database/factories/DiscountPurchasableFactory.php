<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\DiscountPurchasable;
use Lunar\Models\ProductVariant;

class DiscountPurchasableFactory extends Factory
{
    protected $model = DiscountPurchasable::class;

    public function definition(): array
    {
        return [
            'purchasable_id' => ProductVariant::factory(),
            'purchasable_type' => ProductVariant::class,
        ];
    }
}
