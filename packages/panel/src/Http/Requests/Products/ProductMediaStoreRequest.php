<?php

namespace Lunar\Panel\Http\Requests\Products;

use Lunar\Core\Models\Product;
use Lunar\Panel\Http\Requests\Media\MediaStoreRequest;
use Spatie\MediaLibrary\HasMedia;

class ProductMediaStoreRequest extends MediaStoreRequest
{
    protected function mediaModel(): HasMedia
    {
        /** @var Product $product */
        $product = $this->route('product');

        return $product;
    }
}
