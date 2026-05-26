<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

interface SortsProducts
{
    public function execute(CollectionContract $collection): Collection;
}
