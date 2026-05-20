<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Discountable;
use Lunar\Core\Models\ProductVariant;

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
