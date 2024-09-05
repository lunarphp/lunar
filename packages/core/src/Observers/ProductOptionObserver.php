<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\ProductOption as ProductOptionContract;
use Lunar\Models\Contracts\ProductOptionValue as ProductOptionValueContract;
use Lunar\Models\ProductOption;

class ProductOptionObserver
{
    /**
     * Handle the ProductOption "deleting" event.
     *
     * @return void
     */
    public function deleting(ProductOptionContract $productOption)
    {
        /** @var ProductOption $productOption */
        $productOption->products()->detach();
        /** @var ProductOptionValue $optionValue */
        $productOption->values()->each(
            fn (ProductOptionValueContract $optionValue) => $optionValue->delete()
        );
    }
}
