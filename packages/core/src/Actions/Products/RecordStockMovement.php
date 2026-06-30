<?php

namespace Lunar\Core\Actions\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Lunar\Core\Contracts\Actions\Products\RecomputesStockRollup;
use Lunar\Core\Contracts\Actions\Products\RecordsStockMovement;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;

/**
 * The single write seam for the `on_hand` ledger: lock the (variant, location)
 * level, append the movement, bump `on_hand`, refresh the rollup — atomically.
 */
class RecordStockMovement implements RecordsStockMovement
{
    public function __construct(
        protected RecomputesStockRollup $recomputeRollup,
    ) {}

    public function execute(
        ProductVariant $variant,
        Location $location,
        int $quantity,
        StockMovementType $type,
        ?Model $source = null,
        ?string $note = null,
    ): StockMovement {
        /** @var ProductVariant $variant */
        return DB::transaction(function () use ($variant, $location, $quantity, $type, $source, $note) {
            // Ensure the level row exists (the unique index serialises racing creates),
            // then take the row lock so concurrent movements against it serialise.
            StockLevel::query()->firstOrCreate([
                'product_variant_id' => $variant->getKey(),
                'location_id' => $location->getKey(),
            ]);

            $level = StockLevel::query()
                ->where('product_variant_id', $variant->getKey())
                ->where('location_id', $location->getKey())
                ->lockForUpdate()
                ->first();

            $level->forceFill(['on_hand' => $level->on_hand + $quantity])->save();

            $causer = auth()->user();

            $movement = StockMovement::query()->create([
                'product_variant_id' => $variant->getKey(),
                'location_id' => $location->getKey(),
                'quantity' => $quantity,
                'type' => $type,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'note' => $note,
                'causer_type' => $causer?->getMorphClass(),
                'causer_id' => $causer?->getKey(),
            ]);

            $this->recomputeRollup->execute($variant);

            return $movement;
        });
    }
}
