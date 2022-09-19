<?php

namespace Lunar\Observers;

use Lunar\Jobs\Collections\UpdateProductPositions;
use Lunar\Models\Collection;

class CollectionObserver
{
    /**
     * Handle the Collection "updated" event.
     *
     * @param  \Lunar\Models\Collection  $collection
     * @return void
     */
    public function updated(Collection $collection)
    {
        UpdateProductPositions::dispatch($collection);
    }
}
