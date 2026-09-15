<?php

namespace Lunar\Bundles\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Events\Concerns\SerializesInvalidatedModel;

/**
 * A bundle's definition changed. Fired once per bundle per flush, after the
 * change commits. Tags and key are captured here so the event stands alone
 * even when the bundle has been deleted or the listener runs on a queue.
 */
class BundleInvalidated implements CacheInvalidationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesInvalidatedModel;

    /** @var array<string> */
    public readonly array $cacheTags;

    public readonly string $morphType;

    public readonly int|string $cacheKey;

    public function __construct(
        public Bundle $bundle,
        public CacheInvalidationReason $reason,
    ) {
        $this->cacheTags = $bundle->cacheTags();
        $this->morphType = $bundle->getMorphClass();
        $this->cacheKey = $bundle->cacheKey();
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

    public function cacheModel(): Model
    {
        return $this->bundle;
    }
}
