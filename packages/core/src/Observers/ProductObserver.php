<?php

namespace GetCandy\Observers;

use GetCandy\Models\Collection;
use GetCandy\Models\Language;
use GetCandy\Models\Product;
use Illuminate\Support\Str;

class ProductObserver
{
    /**
     * Handle the collection "created" event.
     *
     * @param  Collection  $collection
     * @return void
     */
    public function created(Product $product)
    {
        if (! $product->urls()->count() && $language = Language::getDefault()) {
            $product->urls()->create([
                'slug' => Str::slug($product->translateAttribute('name')),
                'default' => true,
                'language_id' => $language->id,
            ]);
        }
    }
}
