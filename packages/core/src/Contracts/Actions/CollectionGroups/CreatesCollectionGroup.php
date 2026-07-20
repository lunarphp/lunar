<?php

namespace Lunar\Core\Contracts\Actions\CollectionGroups;

use Lunar\Core\Models\CollectionGroup;

interface CreatesCollectionGroup
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): CollectionGroup;
}
