<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface OrderAddress
{
    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo;
    
    /**
     * Return the country relationship.
     */
    public function country(): BelongsTo;
}
