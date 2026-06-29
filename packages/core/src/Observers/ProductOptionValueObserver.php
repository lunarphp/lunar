<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\ProductOptionValue;

class ProductOptionValueObserver
{
    /**
     * Handle the ProductOptionValue "deleting" event.
     *
     * @return void
     */
    public function deleting(ProductOptionValue $productOptionValue)
    {
        /** @var ProductOptionValue $productOptionValue */
        $productOptionValue->variants()->detach();
    }
}
