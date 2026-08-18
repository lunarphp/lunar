<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\ProductVariant;

class CartLineFactory extends BaseFactory
{
    protected $model = CartLine::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'cart_id' => Cart::factory(),
            'quantity' => $this->faker->numberBetween(1, 1000),
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => ProductVariant::factory(),
            'meta' => null,
        ];
    }
}
