<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\ProductVariant;

class ProductVariantObserver
{
    public function deleting(ProductVariant $productVariant): void
    {
        // Guarded here so every delete path refuses while the variant appears
        // on an order line — disable it instead. The last-variant rule lives
        // in DeleteProductVariant only, so deleting a whole product can still
        // cascade through its final variant.
        if ($productVariant->hasOrderHistory()) {
            throw new ProductActionException(
                'Variant has order history — disable it instead of deleting.'
            );
        }

        /** @var ProductVariant $productVariant */
        $productVariant->prices()->delete();
        $productVariant->values()->detach();
        $productVariant->images()->detach();
    }
}
