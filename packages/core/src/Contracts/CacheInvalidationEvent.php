<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Enums\CacheInvalidationReason;

/**
 * Implemented by every cache-invalidation event. A consumer can listen narrowly
 * (one event type) or broadly (this interface — Laravel dispatches to listeners
 * registered against an event's implemented interfaces). The scalar accessors
 * are authoritative: they are captured at record time so a delete invalidation,
 * and any queued listener, works without rehydrating a row that no longer exists.
 */
interface CacheInvalidationEvent
{
    /**
     * The cache tags a storefront keys this entity's pages on.
     *
     * @return array<string>
     */
    public function cacheTags(): array;

    /** The morph alias of the invalidated entity (e.g. "product"). */
    public function morphType(): string;

    /** The invalidated entity's cache key. */
    public function cacheKey(): int|string;

    /** Why the entity was invalidated. */
    public function reason(): CacheInvalidationReason;
}
