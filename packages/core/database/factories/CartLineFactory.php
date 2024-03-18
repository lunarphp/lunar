<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\ProductVariant;

class CartLineFactory extends BaseFactory
{
    protected $model = CartLine::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'purchasable_type' => (new ProductVariant)->getMorphClass(),
            'purchasable_id' => ProductVariant::factory(),
            'meta' => null,
        ];
    }
}
