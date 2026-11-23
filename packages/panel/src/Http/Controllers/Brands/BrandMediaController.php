<?php

namespace Lunar\Panel\Http\Controllers\Brands;

use Illuminate\Http\JsonResponse;
use Lunar\Core\Models\Brand;
use Lunar\Panel\Http\Controllers\Concerns\StagesMedia;
use Lunar\Panel\Http\Requests\Brands\BrandMediaStoreRequest;

class BrandMediaController
{
    use StagesMedia;

    public function store(BrandMediaStoreRequest $request, Brand $brand): JsonResponse
    {
        return $this->stage($request, $brand);
    }
}
