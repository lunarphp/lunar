<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;

interface CreatesRootCollection
{
    public function execute(int $collectionGroupId, string|array $name): Collection;
}
