<?php

namespace Lunar\Core\Contracts\Actions\CollectionGroups;

use Lunar\Core\Models\CollectionGroup;

interface UpdatesCollectionGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(CollectionGroup $group, array $attributes): CollectionGroup;
}
