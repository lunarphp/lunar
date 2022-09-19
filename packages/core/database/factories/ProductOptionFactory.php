<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\ProductOption;

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
