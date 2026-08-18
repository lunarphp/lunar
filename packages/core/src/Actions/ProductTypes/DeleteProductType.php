<?php

namespace Lunar\Core\Actions\ProductTypes;

use Lunar\Core\Contracts\Actions\ProductTypes\DeletesProductType;
use Lunar\Core\Exceptions\ProductTypeActionException;
use Lunar\Core\Models\ProductType;

/**
 * Delete a product type. Refused while the type still has products —
 * reassign or remove them first (the model's deleting hook enforces the same
 * rule for every other delete path). The attribute-mapping pivot detaches via
 * the same hook.
 */
class DeleteProductType implements DeletesProductType
{
    public static function isProtected(ProductType $productType): bool
    {
        return $productType->products()->exists();
    }

    public function execute(ProductType $productType): void
    {
        if (static::isProtected($productType)) {
            throw new ProductTypeActionException(
                'Product type has products — reassign or remove them before deleting.'
            );
        }

        $productType->delete();
    }
}
