<?php

namespace Lunar\Panel\Http\Requests\ProductTypes;

use Lunar\Core\Models\ProductType;
use Lunar\Panel\Http\Requests\Media\MediaStoreRequest;
use Spatie\MediaLibrary\HasMedia;

class ProductTypeMediaStoreRequest extends MediaStoreRequest
{
    protected function mediaModel(): HasMedia
    {
        /** @var ProductType $productType */
        $productType = $this->route('productType');

        return $productType;
    }
}
