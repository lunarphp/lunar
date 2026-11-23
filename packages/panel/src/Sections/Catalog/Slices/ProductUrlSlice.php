<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\Product;

class ProductUrlSlice extends UrlSlice
{
    public function model(): string
    {
        return Product::class;
    }
}
