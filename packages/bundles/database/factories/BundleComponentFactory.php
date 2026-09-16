<?php

namespace Lunar\Bundles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Core\Models\ProductVariant;

class BundleComponentFactory extends Factory
{
    protected $model = BundleComponent::class;

    public function definition(): array
    {
        return [
            'bundle_id' => Bundle::factory(),
            'bundle_group_id' => null,
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => 1,
            'default' => false,
            'position' => 0,
        ];
    }
}
