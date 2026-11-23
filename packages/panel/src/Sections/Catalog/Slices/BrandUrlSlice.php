<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\Brand;

class BrandUrlSlice extends UrlSlice
{
    public function model(): string
    {
        return Brand::class;
    }
}
