<?php

namespace Lunar\Core\Models\Relations;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Records cache invalidation when a many-to-many relationship is mutated through
 * the native Eloquent API (`attach`/`detach`/`sync`/`toggle`/`updateExistingPivot`).
 * Pivot writes fire no model event, so this is the seam that keeps invalidation
 * complete without asking a developer to learn a Lunar-specific verb. Recording
 * is skipped for models that do not participate in cache invalidation.
 */
trait RecordsCacheInvalidation
{
    public function attach($id, array $attributes = [], $touch = true)
    {
        parent::attach($id, $attributes, $touch);

        $this->recordCacheInvalidation($this->parseIds($id));
    }

    public function detach($ids = null, $touch = true)
    {
        // Capture the affected keys before the detach; a detach-all empties the set.
        $affected = $ids === null ? $this->allRelatedIds()->all() : $this->parseIds($ids);

        $result = parent::detach($ids, $touch);

        $this->recordCacheInvalidation($affected);

        return $result;
    }

    public function sync($ids, $detaching = true)
    {
        $changes = parent::sync($ids, $detaching);

        $this->recordCacheInvalidation(array_merge(
            $changes['attached'],
            $changes['detached'],
            $changes['updated'],
        ));

        return $changes;
    }

    public function toggle($ids, $touch = true)
    {
        $changes = parent::toggle($ids, $touch);

        $this->recordCacheInvalidation(array_merge(
            $changes['attached'],
            $changes['detached'],
        ));

        return $changes;
    }

    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        $result = parent::updateExistingPivot($id, $attributes, $touch);

        $this->recordCacheInvalidation($this->parseIds($id));

        return $result;
    }

    /**
     * @param  array<int, int|string>  $relatedKeys
     */
    protected function recordCacheInvalidation(array $relatedKeys): void
    {
        $invalidator = app(CacheInvalidator::class);

        if ($this->participatesInCacheInvalidation($this->parent)) {
            $invalidator->record($this->parent, CacheInvalidationReason::RelatedChanged);
        }

        $relatedKeys = array_filter($relatedKeys);

        if ($relatedKeys === [] || ! $this->participatesInCacheInvalidation($this->getRelated())) {
            return;
        }

        foreach ($this->getRelated()->newQuery()->findMany($relatedKeys) as $related) {
            $invalidator->record($related, CacheInvalidationReason::RelatedChanged);
        }
    }

    protected function participatesInCacheInvalidation(Model $model): bool
    {
        return method_exists($model, 'cacheInvalidationTargets');
    }
}
