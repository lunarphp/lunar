<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFeatureFactory extends Factory
{
    protected $model = ProductFeature::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->name,
            ],
        ];
    }
}
