<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface StockReservation
{
    /**
     * The variant this reservation holds stock for.
     */
    public function variant(): BelongsTo;

    /**
     * The holder the reservation belongs to (a Cart / checkout session).
     */
    public function reference(): MorphTo;

    /**
     * Release the reservation, returning its quantity to availability.
     */
    public function release(): \Lunar\Core\Models\StockReservation;

    /**
     * Convert the reservation to a commitment at order placement (the order
     * line carries the commitment from here; the reserved quantity is freed).
     */
    public function commit(): \Lunar\Core\Models\StockReservation;
}
