<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Discountable;
use Lunar\Models\ProductVariant;

class DiscountableFactory extends BaseFactory
{
    protected $model = Discountable::class;

    public function definition(): array
    {
        return [
            'discountable_id' => ProductVariant::factory(),
            'discountable_type' => ProductVariant::morphName(),
        ];
    }
}
