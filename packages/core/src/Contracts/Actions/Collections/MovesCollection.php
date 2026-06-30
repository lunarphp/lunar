<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;

interface MovesCollection
{
    public function execute(Collection $collection, ?Collection $target = null): Collection;
}
