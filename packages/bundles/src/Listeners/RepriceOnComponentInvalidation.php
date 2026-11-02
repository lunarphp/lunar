<?php

namespace Lunar\Bundles\Listeners;

use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Events\Catalog\ProductInvalidated;

/**
 * A component product changed (a variant deleted, a price edited through a
 * path the price observer cannot see): reprice every `components` bundle that
 * includes one of its variants.
 */
class RepriceOnComponentInvalidation
{
    public function __construct(
        protected RepricesBundle $reprice,
    ) {}

    public function handle(ProductInvalidated $event): void
    {
        Bundle::query()
            ->where('pricing', BundlePricing::Components)
            ->whereHas('components.variant', fn ($query) => $query->where('product_id', $event->product->getKey()))
            ->get()
            ->each(fn (Bundle $bundle) => $this->reprice->execute($bundle));
    }
}
