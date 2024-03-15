<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface TaxRate
{
    /**
     * Return the tax zone relation.
     */
    public function taxZone(): BelongsTo;
    
    /**
     * Return the tax rate amounts relation.
     */
    public function taxRateAmounts(): HasMany;
}
