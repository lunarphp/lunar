<?php

namespace Lunar\Core\Actions\CollectionGroups;

use Lunar\Core\Contracts\Actions\CollectionGroups\UpdatesCollectionGroup;
use Lunar\Core\Models\CollectionGroup;

/**
 * Update a collection group's name and handle.
 */
class UpdateCollectionGroup implements UpdatesCollectionGroup
{
    public function execute(CollectionGroup $group, array $attributes): CollectionGroup
    {
        $group->update($attributes);

        return $group;
    }
}
