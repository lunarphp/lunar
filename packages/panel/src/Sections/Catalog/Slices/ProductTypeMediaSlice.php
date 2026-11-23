<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\ProductType;

class ProductTypeMediaSlice extends MediaSlice
{
    public function model(): string
    {
        return ProductType::class;
    }
}
