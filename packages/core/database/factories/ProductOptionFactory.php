<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->name,
            ],
        ];
    }
}
