<?php

namespace GetCandy\Tests\Stubs\Models;

use Illuminate\Support\Collection;

trait SizesTrait
{
    protected function extendedSizes(): Collection
    {
        return collect(['xl', 'xxl']);
    }
}
