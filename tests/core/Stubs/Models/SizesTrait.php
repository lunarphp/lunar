<?php

namespace Stubs\Models;

use Illuminate\Support\Collection;

trait SizesTrait
{
    public function extendedSizes(): Collection
    {
        return collect(['xl', 'xxl']);
    }
}
