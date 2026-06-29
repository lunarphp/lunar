<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\MovesCollection;
use Lunar\Core\Exceptions\CollectionActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;

/**
 * Re-parent a collection inside its group. Validates the target is not
 * itself (or one of its descendants — which would create a cycle) and lets
 * the nested-set trait recompute `_lft`/`_rgt` for the moved subtree.
 */
class MoveCollection implements MovesCollection
{
    public function execute(Collection $collection, ?Collection $target = null): Collection
    {
        /** @var Collection $collection */
        if ($target !== null) {
            if ($target->is($collection)) {
                throw new CollectionActionException('A collection cannot be moved into itself.');
            }

            /** @var Collection $target */
            if ($target->isDescendantOf($collection)) {
                throw new CollectionActionException('A collection cannot be moved into one of its own descendants.');
            }
        }

        return DB::transaction(function () use ($collection, $target): Collection {
            $collection->parent()->associate($target)->save();

            return $collection;
        });
    }
}
