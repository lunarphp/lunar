<?php

namespace Lunar\Base\Traits;

use Illuminate\Support\Collection;
use Lunar\Jobs\SyncTags;
use Lunar\Models\Tag;

trait HasTags
{
    /**
     * Get the tags
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<Tag>
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

    public function syncTags(Collection $tags, $immediate = false)
    {
        $method = $immediate ? 'dispatchSync' : 'dispatch';

        SyncTags::{$method}($this, $tags);
    }
}
