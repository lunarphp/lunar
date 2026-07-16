<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Marks a model as an independently-cacheable entity: it carries a cache tag,
 * emits a typed invalidation event, and invalidates its own cache on every
 * lifecycle change. Satellites whose changes should invalidate this entity use
 * {@see InvalidatesRelatedCache} instead.
 */
trait InvalidatesCache
{
    public static function bootInvalidatesCache(): void
    {
        static::saved(function (Model $model) {
            app(CacheInvalidator::class)->record(
                $model,
                $model->wasRecentlyCreated ? CacheInvalidationReason::Created : CacheInvalidationReason::Updated,
            );
        });

        static::deleted(function (Model $model) {
            app(CacheInvalidator::class)->record($model, CacheInvalidationReason::Deleted);
        });
    }

    /**
     * The entity's cache key. The seam through which the tag scheme can later
     * move from the primary key to a stable `public_id`.
     */
    public function cacheKey(): int|string
    {
        return $this->getKey();
    }

    /**
     * The cache tags a storefront keys this entity's pages on.
     *
     * @return array<string>
     */
    public function cacheTags(): array
    {
        return ["{$this->getMorphClass()}:{$this->cacheKey()}"];
    }

    /**
     * The cacheable entities invalidated when this model changes.
     *
     * @return iterable<Model>
     */
    public function cacheInvalidationTargets(): iterable
    {
        return [$this];
    }

    /** Build the invalidation event for this entity. */
    abstract public function newCacheInvalidationEvent(CacheInvalidationReason $reason): CacheInvalidationEvent;

    /** Manually invalidate this entity; participates in the same after-commit flush. */
    public function invalidateCache(CacheInvalidationReason $reason = CacheInvalidationReason::Updated): void
    {
        app(CacheInvalidator::class)->record($this, $reason);
    }
}
