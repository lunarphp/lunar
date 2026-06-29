<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface StockLevel
{
    /**
     * The variant this level belongs to.
     */
    public function variant(): BelongsTo;

    /**
     * The location this level is held at.
     */
    public function location(): BelongsTo;

    /**
     * The `on_hand` ledger movements for this level's variant + location.
     */
    public function movements(): HasMany;

    /**
     * The allocatable-physical figure at this location:
     * `on_hand - committed - unavailable`. Reservations are global (never
     * allocated to a location), so the sellable figure is the variant rollup.
     */
    public function getAvailableAttribute(): int;
}
