<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockRollup;
use Lunar\Core\Contracts\Actions\Products\SetsIncomingStock;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;

/**
 * The write seam for the `incoming` bucket: a statement of what a location
 * expects to receive, so it is set absolutely and records no movement — it is
 * not part of the `on_hand` ledger. Locks the level like
 * {@see RecordStockMovement} so it cannot race a concurrent movement's rollup.
 */
class SetIncomingStock implements SetsIncomingStock
{
    public function __construct(
        protected RecomputesStockRollup $recomputeRollup,
    ) {}

    public function execute(ProductVariant $variant, Location $location, int $quantity): StockLevel
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Incoming stock cannot be negative.');
        }

        return DB::transaction(function () use ($variant, $location, $quantity) {
            StockLevel::query()->firstOrCreate([
                'product_variant_id' => $variant->getKey(),
                'location_id' => $location->getKey(),
            ]);

            /** @var StockLevel $level */
            $level = StockLevel::query()
                ->where('product_variant_id', $variant->getKey())
                ->where('location_id', $location->getKey())
                ->lockForUpdate()
                ->first();

            $level->forceFill(['incoming' => $quantity])->save();

            $this->recomputeRollup->execute($variant);

            return $level;
        });
    }
}
