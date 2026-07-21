<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\DeletesProductVariant;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\ProductVariant;

/**
 * Delete a single variant. Refused while the variant appears on an order
 * line (disable it instead) or is the product's last variant — a product
 * always keeps at least one. The order-history rule is also enforced by the
 * observer for every other delete path; the last-variant rule lives here
 * only, so deleting the whole product can still cascade through its final
 * variant.
 */
class DeleteProductVariant implements DeletesProductVariant
{
    public function execute(ProductVariant $variant): void
    {
        if ($variant->hasOrderHistory()) {
            throw new ProductActionException(
                'Variant has order history — disable it instead of deleting.'
            );
        }

        $isLast = ! $variant->product()->first()
            ?->variants()
            ->whereKeyNot($variant->id)
            ->exists();

        if ($isLast) {
            throw new ProductActionException(
                'Products keep at least one variant — delete the product instead.'
            );
        }

        $variant->delete();
    }
}
