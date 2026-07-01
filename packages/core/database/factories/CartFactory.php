<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;

class CartFactory extends BaseFactory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
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
