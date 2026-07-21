<?php

namespace Lunar\Core\Database\Factories;

use Illuminate\Support\Str;
use Lunar\Core\Enums\SellingPolicy;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxRateAmount;

class ProductVariantFactory extends BaseFactory
{
    protected $model = ProductVariant::class;

    /**
     * Seed on_hand stock at a location (the default one unless given) via a
     * Received movement — stock is ledger-derived, so there is no column to set.
     */
    public function inStock(int $quantity = 100, ?Location $location = null): static
    {
        return $this->afterCreating(function (ProductVariant $variant) use ($quantity, $location) {
            $location ??= Location::query()->where('default', true)->first()
                ?? Location::factory()->create(['default' => true]);

            $variant->adjustStock($location, $quantity, StockMovementType::Received);
        });
    }

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'product_id' => Product::factory(),
            'tax_class_id' => TaxClass::factory()->hasTaxRateAmounts(
                TaxRateAmount::factory()
            ),
            'sku' => Str::random(12),
            'unit_quantity' => 1,
            'gtin' => $this->faker->unique()->isbn13,
            'mpn' => $this->faker->unique()->isbn13,
            'ean' => $this->faker->unique()->ean13,
            'enabled' => true,
            'shippable' => true,
            'selling_policy' => SellingPolicy::Always,
        ];
    }
}
