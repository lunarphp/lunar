<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;

interface MovesCollection
{
    /**
     * Re-parent a collection, optionally into a different collection group.
     * A null target makes the collection a root; a null group keeps it in
     * its current one. The collection's subtree always moves with it.
     */
    public function execute(Collection $collection, ?Collection $target = null, ?CollectionGroup $group = null): Collection;
}
