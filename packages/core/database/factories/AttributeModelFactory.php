<?php

namespace Lunar\Core\Database\Factories;

use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeModel;
use Lunar\Core\Models\Product;

class AttributeModelFactory extends BaseFactory
{
    protected $model = AttributeModel::class;

    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'model_type' => Product::morphName(),
        ];
    }
}
