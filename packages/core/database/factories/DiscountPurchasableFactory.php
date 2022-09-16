<?php

namespace Lunar\Database\Factories;

use Lunar\Models\DiscountPurchasable;
use Lunar\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

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
