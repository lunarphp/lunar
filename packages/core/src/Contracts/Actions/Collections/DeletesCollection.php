<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;

interface DeletesCollection
{
    public function execute(Collection $collection, ?Collection $reparentTo = null): bool;
}
