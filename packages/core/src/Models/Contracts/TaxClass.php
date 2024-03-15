<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface TaxClass
{
    /**
     * Return the tax rate amounts relationship.
     */
    public function taxRateAmounts(): HasMany;
    
    /**
     * Return the ProductVariants relationship.
     */
    public function productVariants(): HasMany;
}
