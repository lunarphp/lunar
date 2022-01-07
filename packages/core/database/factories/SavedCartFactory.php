<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Cart;
use GetCandy\Models\SavedCart;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedCartFactory extends Factory
{
    protected $model = SavedCart::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'name'    => $this->faker->name,
        ];
    }
}
