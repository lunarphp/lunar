<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'tax_class_id' => TaxClass::factory()->hasTaxRateAmounts(
                TaxRateAmount::factory()
            ),
            'sku' => Str::random(12),
            'unit_quantity' => 1,
            'gtin' => $this->faker->unique()->isbn13,
            'mpn' => $this->faker->unique()->isbn13,
            'ean' => $this->faker->unique()->ean13,
            'shippable' => true,
        ];
    }
}
