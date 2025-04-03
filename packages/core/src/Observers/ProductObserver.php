<?php

namespace Lunar\Observers;

use Lunar\Models\Product;

class ProductObserver
{
    /**
     * Handle the ProductVariant "deleted" event.
     */
    public function deleting(Product $product): void
    {
        if ($product->isForceDeleting()) {
            $product->variants()->withTrashed()->get()->each->forceDelete();

            $product->collections()->detach();

            $product->customerGroups()->detach();

            $product->urls()->delete();

            $product->productOptions()->detach();

            $product->associations()->delete();

            $product->channels()->detach();

            $product->tags()->detach();
        } else {
            $product->variants()->get()->each->delete();
        }
    }

    public function restored(Product $product): void
    {
        $product->variants()->withTrashed()->get()->each->restore();
    }
}
