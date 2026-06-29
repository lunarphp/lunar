<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\StockMovement;

interface ProductVariant
{
    /**
     * The related product.
     */
    public function product(): BelongsTo;

    /**
     * Return the tax class relationship.
     */
    public function taxClass(): BelongsTo;

    /**
     * Return the related product option values.
     */
    public function values(): BelongsToMany;

    /**
     * The variant's per-location stock balances.
     */
    public function stockLevels(): HasMany;

    /**
     * The variant's `on_hand` ledger across every location.
     */
    public function stockMovements(): HasMany;

    /**
     * Record a signed `on_hand` movement at a location, appending to the ledger
     * and refreshing the rollup.
     */
    public function adjustStock(Location $location, int $quantity, StockMovementType $type, ?Model $source = null, ?string $note = null): StockMovement;
}
