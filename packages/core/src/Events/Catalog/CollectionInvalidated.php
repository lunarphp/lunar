<?php

namespace Lunar\Core\Events\Catalog;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\Collection;

/**
 * A collection's display data is stale. Fired once per collection per flush,
 * after the change commits. A re-parent also fires this for each descendant.
 */
class CollectionInvalidated implements CacheInvalidationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var array<string> */
    public readonly array $cacheTags;

    public readonly string $morphType;

    public readonly int|string $cacheKey;

    public function __construct(
        public Collection $collection,
        public CacheInvalidationReason $reason,
    ) {
        $this->cacheTags = $collection->cacheTags();
        $this->morphType = $collection->getMorphClass();
        $this->cacheKey = $collection->cacheKey();
    }

    /** @return array<string> */
    public function cacheTags(): array
    {
        return $this->cacheTags;
    }

    public function morphType(): string
    {
        return $this->morphType;
    }

    public function cacheKey(): int|string
    {
        return $this->cacheKey;
    }

    public function reason(): CacheInvalidationReason
    {
        return $this->reason;
    }
}
