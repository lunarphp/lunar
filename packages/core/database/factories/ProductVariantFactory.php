<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Models\TaxClass;
use GetCandy\Models\TaxRateAmount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id'   => Product::factory(),
            'tax_class_id' => TaxClass::factory()->hasTaxRateAmounts(
                TaxRateAmount::factory()
            ),
            'sku'           => Str::random(12),
            'unit_quantity' => 1,
            'gtin'          => $this->faker->unique()->isbn13,
            'mpn'           => $this->faker->unique()->isbn13,
            'ean'           => $this->faker->unique()->ean13,
            'shippable'     => true,
        ];
    }
}
