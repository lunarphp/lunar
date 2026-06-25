<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Contracts\Actions\Products\RecordsStockMovement;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockLevel;
use Lunar\Core\Models\StockMovement;

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
        return $this->hasMany(StockLevel::modelClass());
    }

    /**
     * The variant's `on_hand` ledger across every location.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::modelClass());
    }

    /**
     * Record a signed `on_hand` movement at a location, appending to the ledger
     * and refreshing the rollup. The model-first seam over {@see RecordsStockMovement}.
     */
    public function adjustStock(Location $location, int $quantity, StockMovementType $type, ?Model $source = null, ?string $note = null): StockMovement
    {
        return app(RecordsStockMovement::class)->execute($this, $location, $quantity, $type, $source, $note);
    }
}
