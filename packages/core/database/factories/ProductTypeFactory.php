<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\ProductType;

class ProductTypeFactory extends Factory
{
    protected $model = ProductType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
