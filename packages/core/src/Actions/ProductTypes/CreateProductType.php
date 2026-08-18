<?php

namespace Lunar\Core\Actions\ProductTypes;

use Lunar\Core\Contracts\Actions\ProductTypes\CreatesProductType;
use Lunar\Core\Models\ProductType;

/**
 * Create a product type and, when given, sync its product/variant attribute
 * mapping. A missing handle is generated from the name by the model's
 * creating hook.
 */
class CreateProductType implements CreatesProductType
{
    public function execute(array $attributes, ?array $attributeIds = null): ProductType
    {
        $productType = ProductType::create($attributes);

        if ($attributeIds !== null) {
            $productType->attributeMapping()->sync($attributeIds);
        }

        return $productType;
    }
}
