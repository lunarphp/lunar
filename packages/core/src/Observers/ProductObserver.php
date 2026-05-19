<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Product as ProductContract;

class ProductObserver
{
    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleting(ProductContract $product): void
    {
        if ($product->isForceDeleting()) {
            $product->variants()->withTrashed()->get()->each->forceDelete();

            $product->collections()->detach();

            $product->customerGroups()->detach();

            $product->urls()->delete();

            $product->productOptions()->detach();

            $product->channels()->detach();

            $product->tags()->detach();
        }

        $product->associations()->delete();
        $product->inverseAssociations()->delete();
    }
}
