<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductFeatureValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFeatureValueFactory extends Factory
{
    private static $position = 1;

    protected $model = ProductFeatureValue::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->name,
            ],
            'position' => self::$position++,
        ];
    }
}
