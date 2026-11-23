<?php

namespace Lunar\Panel\Http\Controllers\ProductTypes;

use Illuminate\Http\JsonResponse;
use Lunar\Core\Models\ProductType;
use Lunar\Panel\Http\Controllers\Concerns\StagesMedia;
use Lunar\Panel\Http\Requests\ProductTypes\ProductTypeMediaStoreRequest;

class ProductTypeMediaController
{
    use StagesMedia;

    public function store(ProductTypeMediaStoreRequest $request, ProductType $productType): JsonResponse
    {
        return $this->stage($request, $productType);
    }
}
