<?php

namespace Lunar\Bundles\Listeners;

use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;

/**
 * Observes `Price`: a saved or deleted price on a component variant reprices
 * every `components` bundle that includes it. Prices the package writes on a
 * bundle variant never match (a bundle variant is never a component).
 */
class RepriceOnComponentPriceChange
{
    public function __construct(
        protected RepricesBundle $reprice,
    ) {}

    public function saved(Price $price): void
    {
        $this->repriceContaining($price);
    }

    public function deleted(Price $price): void
    {
        $this->repriceContaining($price);
    }

    protected function repriceContaining(Price $price): void
    {
        if ($price->priceable_type !== ProductVariant::morphName()) {
            return;
        }

        Bundle::query()
            ->where('pricing', BundlePricing::Components)
            ->whereHas('components', fn ($query) => $query->where('product_variant_id', $price->priceable_id))
            ->get()
            ->each(fn (Bundle $bundle) => $this->reprice->execute($bundle));
    }
}
