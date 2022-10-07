<?php

namespace Lunar\Hub\Http\Livewire\Traits;

trait MapsCollectionTree
{
    /**
     * Map collections so they're ready to be used.
     *
     * @param  \Illuminate\Support\Collection  $collections
     * @return void
     */
    public function mapCollections($collections)
    {
        return $collections->map(function ($collection) {
            return [
                'id' => $collection->id,
                'parent_id' => $collection->parent_id,
                'name' => $collection->translateAttribute('name'),
                'thumbnail' => $collection->thumbnail?->getUrl('small'),
                'children' => [],
                'children_count' => $collection->children_count,
            ];
        })->toArray();
    }
}
