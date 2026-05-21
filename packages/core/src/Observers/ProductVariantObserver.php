<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "deleted" event.
     *
     * @return void
     */
    public function deleting(ProductVariantContract $productVariant)
    {
        if ($productVariant->isForceDeleting()) {
            /** @var ProductVariant $productVariant */
            $productVariant->prices()->delete();
            $productVariant->values()->detach();
            $productVariant->images()->detach();
        }
    }
}
