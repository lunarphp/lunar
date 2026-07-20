<?php

namespace Lunar\Core\Contracts\Actions\CollectionGroups;

use Lunar\Core\Models\CollectionGroup;

interface DeletesCollectionGroup
{
    public function execute(CollectionGroup $group): void;
}
