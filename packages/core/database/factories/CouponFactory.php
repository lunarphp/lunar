<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;

class CouponFactory extends BaseFactory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'discount_id' => Discount::factory()->coupon(),
            'code' => Str::upper(Str::random(10)),
        ];
    }
}
