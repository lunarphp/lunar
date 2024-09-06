<?php

namespace Lunar\Database\Factories;

use Lunar\Models\Product;
use Lunar\Models\ProductAssociation;

class ProductAssociationFactory extends BaseFactory
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
