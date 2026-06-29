<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;

class ProductOptionObserver
{
    /**
     * Handle the ProductOption "deleting" event.
     *
     * @return void
     */
    public function deleting(ProductOption $productOption)
    {
        /** @var ProductOption $productOption */
        $productOption->products()->detach();
        /** @var ProductOptionValue $optionValue */
        $productOption->values()->each(
            fn (ProductOptionValue $optionValue) => $optionValue->delete()
        );
    }
}
