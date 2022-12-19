<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_type_id' => ProductType::factory(),
            'status' => 'published',
            'brand_id' => Brand::factory()->create()->id,
            'attribute_data' => collect([
                'name' => new Text($this->faker->name),
                'description' => new Text($this->faker->sentence),
            ]),
        ];
    }
}
