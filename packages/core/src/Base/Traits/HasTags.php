<?php

namespace GetCandy\Base\Traits;

use GetCandy\Jobs\SyncTags;
use GetCandy\Models\Tag;
use Illuminate\Support\Collection;

trait HasTags
{
    /**
     * Get all of the models channels.
     */
    public function tags()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->morphToMany(
            Tag::class,
            'taggable',
            "{$prefix}taggables"
        )->withTimestamps();
    }

    public function syncTags(Collection $tags)
    {
        SyncTags::dispatch($this, $tags);
    }
}
