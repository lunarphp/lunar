<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\DiscountPurchasable;
use GetCandy\Models\ProductVariant;
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
