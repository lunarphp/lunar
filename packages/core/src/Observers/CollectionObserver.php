<?php

namespace GetCandy\Observers;

use GetCandy\Jobs\Collections\UpdateProductPositions;
use GetCandy\Models\Collection;
use GetCandy\Models\Language;
use Illuminate\Support\Str;

class CollectionObserver
{
    /**
     * Handle the collection "created" event.
     *
     * @param Collection $collection
     * @return void
     */
    public function created(Collection $collection)
    {
        if (!$collection->urls()->count() && $language = Language::getDefault()) {
            $collection->urls()->create([
                'slug' => Str::slug($collection->translateAttribute('name')),
                'default' => true,
                'language_id' => $language->id,
            ]);
        }
    }

    /**
     * Handle the Collection "updated" event.
     *
     * @param  \GetCandy\Models\Collection  $collection
     * @return void
     */
    public function updated(Collection $collection)
    {
        UpdateProductPositions::dispatch($collection);
    }
}
