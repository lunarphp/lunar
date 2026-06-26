<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface StockMovement
{
    /**
     * The variant the movement applied to.
     */
    public function variant(): BelongsTo;

    /**
     * The location the movement applied at.
     */
    public function location(): BelongsTo;

    /**
     * The originating record — a Fulfilment, a refund Transaction, … (nullable).
     */
    public function source(): MorphTo;

    /**
     * Who triggered the movement (nullable).
     */
    public function causer(): MorphTo;
}
