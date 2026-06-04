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
     * Return the fulfilment lines relationship.
     */
    public function lines(): HasMany;
}
