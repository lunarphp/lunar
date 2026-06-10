<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface Fulfilment
{
    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo;

    /**
     * Return the location relationship.
     */
    public function location(): BelongsTo;

    /**
     * Return the fulfilment lines relationship.
     */
    public function lines(): HasMany;

    /**
     * Return the tracking references relationship.
     */
    public function trackings(): HasMany;

    /**
     * Whether the fulfilment is currently on hold (blocked from shipping).
     */
    public function isOnHold(): bool;

    /**
     * The human-readable label for the current hold reason, resolved from the
     * configured reason list (falls back to the stored key).
     */
    public function holdReasonLabel(): ?string;
}
