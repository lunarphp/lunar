<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "deleted" event.
     *
     * @return void
     */
    public function deleting(ProductVariantContract $productVariant)
    {
        /** @var ProductVariant $productVariant */
        $productVariant->prices()->delete();
        $productVariant->values()->detach();
        $productVariant->images()->detach();
    }
}
