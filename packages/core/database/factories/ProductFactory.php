<?php

namespace Lunar\Database\Factories;

use Lunar\FieldTypes\Text;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_type_id' => ProductType::factory(),
            'status'          => 'published',
            'brand'           => $this->faker->company,
            'attribute_data'  => collect([
                'name'        => new Text($this->faker->name),
                'description' => new Text($this->faker->sentence),
            ]),
        ];
    }
}
