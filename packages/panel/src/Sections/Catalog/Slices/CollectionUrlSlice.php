<?php

namespace Lunar\Panel\Sections\Catalog\Slices;

use Lunar\Core\Models\Collection;

class CollectionUrlSlice extends UrlSlice
{
    public function model(): string
    {
        return Collection::class;
    }
}
