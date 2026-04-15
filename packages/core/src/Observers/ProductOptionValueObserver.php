<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\ProductOptionValue as ProductOptionValueContract;
use Lunar\Models\ProductOptionValue;

class ProductOptionValueObserver
{
    /**
     * Handle the ProductOptionValue "deleting" event.
     *
     * @return void
     */
    public function deleting(ProductOptionValueContract $productOptionValue)
    {
        /** @var ProductOptionValue $productOptionValue */
        $productOptionValue->variants()->detach();
    }
}
