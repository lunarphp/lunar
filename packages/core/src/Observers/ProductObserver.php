<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Contracts\Product as ProductContract;

class ProductObserver
{
    public function deleting(ProductContract $product): void
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
