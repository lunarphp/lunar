<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface OrderLine
{
    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo;

    /**
     * Return the fulfilment lines relationship.
     */
    public function fulfilmentLines(): HasMany;

    /**
     * Return the polymorphic relation.
     */
    public function purchasable(): MorphTo;

    /**
     * Return the currency relationship.
     */
    public function currency(): HasOneThrough;
}
