<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\DeletesCollection;
use Lunar\Core\Exceptions\CollectionActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

/**
 * Delete a collection. When the collection has descendants, the caller may
 * pass a `target` to re-parent them under; otherwise the descendants stay
 * in place and the delete is rejected.
 */
class DeleteCollection implements DeletesCollection
{
    public function execute(CollectionContract $collection, ?CollectionContract $reparentTo = null): bool
    {
        /** @var Collection $collection */
        $hasChildren = $collection->children()->exists();

        if ($hasChildren && $reparentTo === null) {
            throw new CollectionActionException(
                'Collection has descendants — pass a re-parent target or delete the descendants first.'
            );
        }

        return DB::transaction(function () use ($collection, $reparentTo, $hasChildren): bool {
            if ($hasChildren && $reparentTo !== null) {
                foreach ($collection->children as $child) {
                    $reparentTo->appendNode($child);
                }
            }

            return (bool) $collection->delete();
        });
    }
}
