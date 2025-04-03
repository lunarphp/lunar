<?php

namespace Lunar\Observers;

use Lunar\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "deleted" event.
     *
     * @return void
     */
    public function deleting(ProductVariant $productVariant)
    {
        if ($productVariant->isForceDeleting()) {
            $productVariant->prices()->delete();
            $productVariant->values()->detach();
            $productVariant->images()->detach();
        }
    }
}
