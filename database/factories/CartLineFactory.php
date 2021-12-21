<?php

namespace GetCandy\Database\Factories;

use App\Models\User;
use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartLineFactory extends Factory
{
    protected $model = CartLine::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'purchasable_type' => ProductVariant::class,
            'purchasable_id' => 1,
            'meta' => null,
        ];
    }
}
