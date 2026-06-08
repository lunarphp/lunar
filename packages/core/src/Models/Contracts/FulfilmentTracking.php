<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface FulfilmentTracking
{
    /**
     * Return the fulfilment relationship.
     */
    public function fulfilment(): BelongsTo;
}
