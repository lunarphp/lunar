<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockRollup;
use Lunar\Core\Contracts\Actions\Products\SyncsStockCommitment;
use Lunar\Core\Enums\FulfilmentStateCategory;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;

/**
 * Recompute a variant's committed stock from the order book — the canonical
 * predicate, in one place.
 *
 * Global `stock_committed` is the quantity on placed, non-cancelled orders that
 * requires fulfilment and has not yet shipped. Per-location `committed` is the
 * subset allocated to an outstanding fulfilment at that location. The gap
 * (global minus the sum of locations) is commitment not yet allocated to any
 * fulfilment — which keeps `global.committed >= sum(location.committed)` true,
 * and makes fulfilment-cancel (de-allocate the location) and order-cancel
 * (release globally) fall out for free.
 */
class SyncStockCommitment implements SyncsStockCommitment
{
    public function __construct(
        protected RecomputesStockRollup $recomputeRollup,
    ) {}

    public function execute(ProductVariant $variant): ProductVariant
    {
        /** @var ProductVariant $variant */
        return DB::transaction(function () use ($variant) {
            $lines = OrderLine::query()
                ->where('purchasable_type', $variant->getMorphClass())
                ->where('purchasable_id', $variant->getKey())
                ->where('requires_fulfilment', true)
                ->whereHas('order', fn ($query) => $query->whereNotNull('placed_at')->whereNull('cancelled_at'))
                ->with(['fulfilmentLines.fulfilment'])
                ->get();

            $globalCommitted = 0;
            $committedByLocation = [];

            foreach ($lines as $line) {
                $shipped = 0;

                foreach ($line->fulfilmentLines as $fulfilmentLine) {
                    $category = $fulfilmentLine->fulfilment->state->category();

                    if ($category === FulfilmentStateCategory::Fulfilled || $category === FulfilmentStateCategory::Returned) {
                        // Already handed over — no longer outstanding commitment.
                        $shipped += $fulfilmentLine->quantity;
                    } elseif ($category === FulfilmentStateCategory::Outstanding) {
                        $locationId = $fulfilmentLine->fulfilment->location_id;
                        $committedByLocation[$locationId] = ($committedByLocation[$locationId] ?? 0) + $fulfilmentLine->quantity;
                    }
                }

                $globalCommitted += max(0, $line->quantity - $shipped);
            }

            $variant->forceFill(['stock_committed' => $globalCommitted])->save();

            $locationIds = collect($committedByLocation)->keys()
                ->merge($variant->stockLevels()->pluck('location_id'))
                ->unique();

            foreach ($locationIds as $locationId) {
                StockLevel::query()->updateOrCreate(
                    ['product_variant_id' => $variant->getKey(), 'location_id' => $locationId],
                    ['committed' => $committedByLocation[$locationId] ?? 0],
                );
            }

            $this->recomputeRollup->execute($variant);

            return $variant;
        });
    }
}
