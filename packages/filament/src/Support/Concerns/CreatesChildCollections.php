<?php

namespace Lunar\Filament\Support\Concerns;

use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;

trait CreatesChildCollections
{
    public function createChildCollection(CollectionContract $parent, array|string $name)
    {
        app(CreatesChildCollection::class)->execute(parent: $parent, name: $name);
    }
}
