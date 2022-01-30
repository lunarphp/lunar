<?php

namespace GetCandy\Observers;

use GetCandy\Jobs\Collections\UpdateProductPositions;
use GetCandy\Models\Collection;

class CollectionObserver
{
    /**
     * Handle the Collection "updated" event.
     *
     * @param  \App\Models\Collection  $collection
     * @return void
     */
    public function updated(Collection $collection)
    {
        UpdateProductPositions::dispatch($collection);
    }
}
