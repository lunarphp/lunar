<?php

namespace Lunar\Bundles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Models\ProductVariant;

class BundleFactory extends Factory
{
    protected $model = Bundle::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'pricing' => BundlePricing::Fixed,
            'discount_percentage' => null,
        ];
    }

    public function componentPriced(?float $discountPercentage = null): static
    {
        return $this->state([
            'pricing' => BundlePricing::Components,
            'discount_percentage' => $discountPercentage,
        ]);
    }
}
