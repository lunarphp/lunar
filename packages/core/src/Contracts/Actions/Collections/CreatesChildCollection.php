<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

interface CreatesChildCollection
{
    public function execute(CollectionContract $parent, string|array $name): Collection;
}
