<?php

namespace Lunar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;

class ProductAssociationFactory extends Factory
{
    protected $model = ProductAssociation::class;

    public function definition(): array
    {
        return [
            'product_parent_id' => Product::factory(),
            'product_target_id' => Product::factory(),
            'type' => 'cross-sell',
        ];
    }
}
