<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;

class DiscountRedemptionFactory extends BaseFactory
{
    protected $model = DiscountRedemption::class;

    public function definition(): array
    {
        return [
            'discount_id' => Discount::factory(),
            'redeemed_at' => now(),
        ];
    }
}
