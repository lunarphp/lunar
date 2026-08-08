<?php

namespace Lunar\Core\Events\Catalog;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Core\Concerns\SerializesInvalidatedModel;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\ProductOption;

/**
 * A shared product option's display data is stale (a label or one of its values
 * changed). Fired once per option per flush, after the change commits. Product
 * pages that render the option invalidate downstream by the option tag.
 */
class ProductOptionInvalidated implements CacheInvalidationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesInvalidatedModel;

    /** @var array<string> */
    public readonly array $cacheTags;

    public readonly string $morphType;

    public readonly int|string $cacheKey;

    public function __construct(
        public ProductOption $productOption,
        public CacheInvalidationReason $reason,
    ) {
        $this->cacheTags = $productOption->cacheTags();
        $this->morphType = $productOption->getMorphClass();
        $this->cacheKey = $productOption->cacheKey();
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
        return $this->productOption;
    }
}
