<?php

namespace Lunar\Bundles\Listeners;

use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Contracts\CacheInvalidator;
use Lunar\Core\Enums\CacheInvalidationReason;
use Lunar\Core\Events\Catalog\ProductInvalidated;

/**
 * Fan a component product's invalidation out to every bundle product that
 * includes one of its variants, so a page cached against the bundle refreshes
 * when a part's price or stock changes. Terminates because a bundle variant
 * is never a component.
 */
class InvalidateContainingBundles
{
    public function __construct(
        protected CacheInvalidator $invalidator,
    ) {}

    public function handle(ProductInvalidated $event): void
    {
        Bundle::query()
            ->whereHas('components.variant', fn ($query) => $query->where('product_id', $event->product->getKey()))
            ->with('variant.product')
            ->get()
            ->each(fn (Bundle $bundle) => $this->invalidator->record($bundle, CacheInvalidationReason::RelatedChanged));
    }
}
