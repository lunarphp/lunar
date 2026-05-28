<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Contracts\ProductVariant as ProductVariantContract;
use Lunar\Core\Models\ProductVariant;

class ProductVariantObserver
{
    public function deleting(ProductVariantContract $productVariant): void
    {
        /** @var ProductVariant $productVariant */
        $productVariant->prices()->delete();
        $productVariant->values()->detach();
        $productVariant->images()->detach();
    }
}
