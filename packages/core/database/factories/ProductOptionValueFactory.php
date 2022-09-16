<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\ProductOptionValue;

class ProductOptionValueFactory extends Factory
{
    protected $model = ProductOptionValue::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->name,
            ],
        ];
    }
}
