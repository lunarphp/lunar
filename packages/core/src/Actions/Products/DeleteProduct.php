<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\DeletesProduct;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Product;

/**
 * Delete a product and its variants. Refused while any variant appears on an
 * order line — archive the product instead, so the merchant can still drill
 * into old orders (the observer enforces the same rule for every other
 * delete path).
 */
class DeleteProduct implements DeletesProduct
{
    public static function isProtected(Product $product): bool
    {
        return $product->hasOrderHistory();
    }

    public function execute(Product $product): void
    {
        if (static::isProtected($product)) {
            throw new ProductActionException(
                'Product has order history — archive it instead of deleting.'
            );
        }

        $product->delete();
    }
}
