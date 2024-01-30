<?php

namespace Lunar\Observers;

use Lunar\Models\ProductOptionValue;

class ProductOptionValueObserver
{
    /**
     * Handle the ProductOptionValue "deleting" event.
     *
     * @return void
     */
    public function deleting(ProductOptionValue $productOptionValue)
    {
        $productOptionValue->variants()->detach();
    }
}
