<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Product;

class ProductObserver
{
    public function deleting(Product $product): void
    {
        $product->variants()->get()->each->delete();
        $product->collections()->detach();
        $product->customerGroups()->detach();
        $product->urls()->delete();
        $product->productOptions()->detach();
        $product->channels()->detach();
        $product->tags()->detach();
        $product->associations()->delete();
        $product->inverseAssociations()->delete();
    }
}
