<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductOptionValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFeatureValueFactory extends Factory
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
