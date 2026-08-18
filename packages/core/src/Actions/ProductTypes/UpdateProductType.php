<?php

namespace Lunar\Core\Actions\ProductTypes;

use Lunar\Core\Contracts\Actions\ProductTypes\UpdatesProductType;
use Lunar\Core\Models\ProductType;

/**
 * Update a product type's attributes and, when an attribute id set is given,
 * sync its product/variant attribute mapping — an empty set clears the
 * mapping, while null leaves it untouched.
 */
class UpdateProductType implements UpdatesProductType
{
    public function execute(ProductType $productType, array $attributes, ?array $attributeIds = null): ProductType
    {
        $productType->update($attributes);

        if ($attributeIds !== null) {
            $productType->attributeMapping()->sync($attributeIds);
        }

        return $productType;
    }
}
