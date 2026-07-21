<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\Product;

class ProductObserver
{
    public function deleting(Product $product): void
    {
        // The guard lives here, not just the admin actions, so every delete
        // path (Eloquent, bulk actions, consumer code) refuses while any
        // variant appears on an order line — archive the product instead.
        if ($product->hasOrderHistory()) {
            throw new ProductActionException(
                'Product has order history — archive it instead of deleting.'
            );
        }

        $product->variants()->get()->each->delete();
        $product->collections()->detach();
        $product->customerGroups()->detach();
        $product->urls()->delete();
        $product->productOptions()->detach();
        $product->channels()->detach();
        $product->tags()->detach();
        $product->associations()->delete();
        $product->inverseAssociations()->delete();
    }
}
