<?php

namespace Lunar\Core\Contracts\Actions\Collections;

use Lunar\Core\Models\Collection;

interface CreatesChildCollection
{
    public function execute(Collection $parent, string|array $name): Collection;
}
