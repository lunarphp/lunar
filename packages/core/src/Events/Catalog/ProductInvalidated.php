<?php

namespace Lunar\Core\Events\Catalog;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Core\Concerns\SerializesInvalidatedModel;
use Lunar\Core\Contracts\CacheInvalidationEvent;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Models\Product;

/**
 * A product's display data is stale. Fired once per product per flush, after the
 * change commits. Tags and key are captured here so the event stands alone even
 * when the product has been deleted or the listener runs on a queue.
 */
class ProductInvalidated implements CacheInvalidationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesInvalidatedModel;

    /** @var array<string> */
    public readonly array $cacheTags;

    public readonly string $morphType;

    public readonly int|string $cacheKey;

    public function __construct(
        public Product $product,
        public CacheInvalidationReason $reason,
    ) {
        $this->cacheTags = $product->cacheTags();
        $this->morphType = $product->getMorphClass();
        $this->cacheKey = $product->cacheKey();
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
        return $this->product;
    }
}
