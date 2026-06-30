<?php

namespace Lunar\Core\Contracts;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Records catalog changes and emits one invalidation event per affected
 * cacheable entity — deduped, and flushed when the surrounding transaction
 * commits (or immediately when none is open).
 */
interface CacheInvalidator
{
    /**
     * Record that a model changed. The model's `cacheInvalidationTargets()`
     * resolve the cacheable entities to invalidate; `$reason` is applied to each.
     */
    public function record(Model $model, CacheInvalidationReason $reason): void;

    /** Dispatch one event per pending entity and clear the buffer. */
    public function flush(): void;
}
