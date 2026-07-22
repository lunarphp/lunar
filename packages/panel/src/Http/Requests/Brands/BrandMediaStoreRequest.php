<?php

namespace Lunar\Panel\Http\Requests\Brands;

use Lunar\Core\Models\Brand;
use Lunar\Panel\Http\Requests\Media\MediaStoreRequest;
use Spatie\MediaLibrary\HasMedia;

class BrandMediaStoreRequest extends MediaStoreRequest
{
    protected function mediaModel(): HasMedia
    {
        /** @var Brand $brand */
        $brand = $this->route('brand');

        return $brand;
    }
}
