<?php

namespace Lunar\Database\Factories;

use Lunar\Models\ProductType;

class ProductTypeFactory extends BaseFactory
{
    protected $model = ProductType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
