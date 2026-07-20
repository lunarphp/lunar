<?php

namespace Lunar\Core\Actions\CollectionGroups;

use Lunar\Core\Contracts\Actions\CollectionGroups\DeletesCollectionGroup;
use Lunar\Core\Exceptions\CollectionGroupActionException;
use Lunar\Core\Models\CollectionGroup;

/**
 * Delete a collection group. Refused while the group still has collections —
 * move or delete them first (the model's deleting hook enforces the same
 * rule for every other delete path).
 */
class DeleteCollectionGroup implements DeletesCollectionGroup
{
    public static function isProtected(CollectionGroup $group): bool
    {
        return $group->collections()->exists();
    }

    public function execute(CollectionGroup $group): void
    {
        if (static::isProtected($group)) {
            throw new CollectionGroupActionException(
                'Collection group has collections — move or delete them before deleting.'
            );
        }

        $group->delete();
    }
}
