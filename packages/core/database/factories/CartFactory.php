<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id'      => null,
            'merged_id'    => null,
            'currency_id'  => Currency::factory(),
            'channel_id'   => Channel::factory(),
            'coupon_code'  => $this->faker->boolean ? $this->faker->word : null,
            'completed_at' => null,
            'meta'         => [],
        ];
    }
}
