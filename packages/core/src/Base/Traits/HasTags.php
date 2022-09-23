<?php

namespace Lunar\Base\Traits;

use Illuminate\Support\Collection;
use Lunar\Jobs\SyncTags;
use Lunar\Models\Tag;

trait HasTags
{
    /**
     * Get all of the models channels.
     */
    public function tags()
    {
        $prefix = config('lunar.database.table_prefix');

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
