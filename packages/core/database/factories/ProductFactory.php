<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;

class ProductFactory extends BaseFactory
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
