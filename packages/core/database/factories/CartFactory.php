<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Tests\Core\Stubs\Models\Cart;

class CartFactory extends BaseFactory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'customer_id' => null,
            'merged_id' => null,
            'currency_id' => Currency::factory(),
            'channel_id' => Channel::factory(),
            'completed_at' => null,
            'meta' => [],
        ];
    }
}
