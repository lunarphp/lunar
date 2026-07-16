<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\Concerns\Searchable;

/**
 * Keeps the search index fresh for the cascade cases Scout's own model observer
 * cannot see. A direct create/update/delete already reindexes through Scout, so
 * only `RelatedChanged` (a variant, price, pivot, ... changed) needs handling —
 * which also makes cascade reindexing work for programmatic changes, not just
 * the admin UI.
 */
class ReindexOnCacheInvalidation
{
    public function handle(CacheInvalidationEvent $event): void
    {
        if ($event->reason() !== CacheInvalidationReason::RelatedChanged) {
            return;
        }

        $model = $event->cacheModel();

        if (in_array(Searchable::class, class_uses_recursive($model), true)) {
            $model->searchable();
        }
    }
}
