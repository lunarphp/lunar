<?php

namespace GetCandy\Database\Factories;

use GetCandy\Models\Product;
use GetCandy\Models\ProductAssociation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductAssociationFactory extends Factory
{
    protected $model = ProductAssociation::class;

    public function definition(): array
    {
        return [
            'product_parent_id' => Product::factory(),
            'product_target_id' => Product::factory(),
            'type'              => 'cross-sell',
        ];
    }
}
