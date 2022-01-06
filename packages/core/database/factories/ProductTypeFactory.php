<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
