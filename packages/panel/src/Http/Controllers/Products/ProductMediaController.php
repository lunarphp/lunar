<?php

namespace Lunar\Panel\Http\Controllers\Products;

use Illuminate\Http\JsonResponse;
use Lunar\Core\Models\Product;
use Lunar\Panel\Http\Controllers\Concerns\StagesMedia;
use Lunar\Panel\Http\Requests\Products\ProductMediaStoreRequest;

class ProductMediaController
{
    use StagesMedia;

    public function store(ProductMediaStoreRequest $request, Product $product): JsonResponse
    {
        return $this->stage($request, $product);
    }
}
