<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\Brand;

class BrandMediaSlice extends MediaSlice
{
    public function model(): string
    {
        return Brand::class;
    }
}
