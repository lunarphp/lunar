<?php

namespace Lunar\Core\Events\Catalog;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\Brand;

/**
 * A brand's display data is stale. Fired once per brand per flush, after the
 * change commits. Dependent product pages invalidate downstream by the brand tag.
 */
class BrandInvalidated implements CacheInvalidationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var array<string> */
    public readonly array $cacheTags;

    public readonly string $morphType;

    public readonly int|string $cacheKey;

    public function __construct(
        public Brand $brand,
        public CacheInvalidationReason $reason,
    ) {
        $this->cacheTags = $brand->cacheTags();
        $this->morphType = $brand->getMorphClass();
        $this->cacheKey = $brand->cacheKey();
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
        return $this->brand;
    }
}
