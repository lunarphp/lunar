<?php

namespace Lunar\Database\Factories;

use Lunar\Models\DiscountPurchasable;
use Lunar\Models\ProductVariant;

class DiscountPurchasableFactory extends BaseFactory
{
    protected $model = DiscountPurchasable::class;

    public function definition(): array
    {
        return [
            'purchasable_id' => ProductVariant::factory(),
            'purchasable_type' => ProductVariant::morphName(),
        ];
    }
}
