<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\ProductType;
use Lunar\FieldTypes\Text;

class ProductTypeFactory extends Factory
{
    protected $model = ProductType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'attribute_data' => collect([
                'description' => new Text($this->faker->sentence),
            ]),
        ];
    }
}
