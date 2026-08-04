<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;

class ProductFactory extends BaseFactory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'product_type_id' => ProductType::factory(),
            'status' => 'published',
            'brand_id' => Brand::factory(),
            'name' => collect(['en' => $this->faker->words(3, true)]),
            'description' => collect(['en' => $this->faker->paragraph]),
            'short_description' => collect(['en' => $this->faker->sentence]),
            'attribute_data' => collect(),
        ];
    }
}
