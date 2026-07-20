<?php

namespace Lunar\Core\Actions\CollectionGroups;

use Lunar\Core\Contracts\Actions\CollectionGroups\CreatesCollectionGroup;
use Lunar\Core\Models\CollectionGroup;

/**
 * Create a collection group. A missing handle is generated from the name by
 * the model's creating hook.
 */
class CreateCollectionGroup implements CreatesCollectionGroup
{
    public function execute(array $attributes): CollectionGroup
    {
        return CollectionGroup::create($attributes);
    }
}
