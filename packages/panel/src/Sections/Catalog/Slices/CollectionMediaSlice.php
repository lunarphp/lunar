<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\Collection;

class CollectionMediaSlice extends MediaSlice
{
    public function model(): string
    {
        return Collection::class;
    }
}
