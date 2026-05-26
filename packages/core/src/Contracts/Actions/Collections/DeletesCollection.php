<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Contracts\Collection as CollectionContract;

interface DeletesCollection
{
    public function execute(CollectionContract $collection, ?CollectionContract $reparentTo = null): bool;
}
