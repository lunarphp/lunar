<?php

namespace Lunar\Observers;

use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

class ProductOptionObserver
{
    /**
     * Handle the ProductOption "deleting" event.
     *
     * @return void
     */
    public function deleting(ProductOption $productOption)
    {
        $productOption->products()->detach();
        $productOption->values()->each(
            fn (ProductOptionValue $optionValue) => $optionValue->delete()
        );
    }
}
