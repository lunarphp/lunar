<?php

namespace Lunar\Observers;

use Lunar\Models\ProductOption;

class ProductOptionObserver
{
    /**
     * Handle the ProductVariant "deleted" event.
     *
     * @return void
     */
    public function deleting(ProductOption $productOption)
    {
        $productOption->values()->delete();
    }
}
