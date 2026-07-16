<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Marks a satellite model that has no cache identity of its own but whose
 * changes invalidate one or more {@see InvalidatesCache} entities — a variant or
 * price invalidates its product, an association invalidates both products. The
 * model declares those targets via `cacheInvalidationTargets()`.
 */
trait InvalidatesRelatedCache
{
    public static function bootInvalidatesRelatedCache(): void
    {
        $record = fn (Model $model) => app(CacheInvalidator::class)
            ->record($model, CacheInvalidationReason::RelatedChanged);

        static::saved($record);
        static::deleted($record);
    }

    /**
     * The cacheable entities invalidated when this model changes.
     *
     * @return iterable<Model>
     */
    abstract public function cacheInvalidationTargets(): iterable;
}
