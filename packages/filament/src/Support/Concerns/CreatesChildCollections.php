<?php

namespace Lunar\Filament\Support\Concerns;

use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Models\Collection;

trait CreatesChildCollections
{
    public function createChildCollection(Collection $parent, array|string $name)
    {
        app(CreatesChildCollection::class)->execute(parent: $parent, name: $name);
    }
}
