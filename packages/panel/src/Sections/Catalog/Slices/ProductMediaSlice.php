<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\Product;

class ProductMediaSlice extends MediaSlice
{
    public function model(): string
    {
        return Product::class;
    }
}
