<?php

namespace Lunar\Core\Models\Concerns;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Contracts\Actions\Products\RecordsStockMovement;
use Lunar\Core\Contracts\Actions\Products\ReservesStock;
use Lunar\Core\Contracts\Actions\Products\SyncsStockCommitment;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;
use Lunar\Core\Models\StockReservation;

/**
 * Stock relations and the model-first movement verb for a stock-holding variant.
 *
 * The cached rollup (`stock_on_hand`, `stock_available`, …) lives as columns on
 * the variant; these relations expose the per-location detail behind it.
 */
trait HasStock
{
    /**
     * The variant's per-location stock balances.
     */
    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    /**
     * The variant's `on_hand` ledger across every location.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Record a signed `on_hand` movement at a location, appending to the ledger
     * and refreshing the rollup. The model-first seam over {@see RecordsStockMovement}.
     */
    public function adjustStock(Location $location, int $quantity, StockMovementType $type, ?Model $source = null, ?string $note = null): StockMovement
    {
        return app(RecordsStockMovement::class)->execute($this, $location, $quantity, $type, $source, $note);
    }

    /**
     * {@inheritDoc}
     *
     * The built-in `TracksStock` implementation: recompute committed from the
     * order book via the canonical predicate.
     */
    public function syncStockCommitment(): void
    {
        app(SyncsStockCommitment::class)->execute($this);
    }

    /**
     * {@inheritDoc}
     *
     * The built-in `TracksStock` implementation: a `StockReservation` row backed
     * by the global `stock_reserved` rollup.
     */
    public function reserveStock(int $quantity, ?DateTimeInterface $expiresAt = null, ?Model $reference = null, ?string $note = null): StockReservation
    {
        return app(ReservesStock::class)->execute($this, $quantity, $expiresAt, $reference, $note);
    }
}
