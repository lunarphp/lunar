<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Contracts\ProductOption as ProductOptionContract;
use Lunar\Core\Models\Contracts\ProductOptionValue as ProductOptionValueContract;
use Lunar\Core\Models\ProductOption;

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
