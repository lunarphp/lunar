<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

interface MovesCollection
{
    public function execute(CollectionContract $collection, ?CollectionContract $target = null): Collection;
}
