<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface FulfilmentLine
{
    /**
     * Return the fulfilment relationship.
     */
    public function fulfilment(): BelongsTo;

    /**
     * Return the order line relationship.
     */
    public function orderLine(): BelongsTo;
}
