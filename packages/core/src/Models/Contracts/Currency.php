<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface Currency
{
    public function scopeEnabled(Builder $query, $enabled = true): Builder;

    /**
     * Return the prices relationship
     */
    public function prices(): HasMany;

    /**
     * Returns the amount we need to multiply or divide the price
     * for the cents/pence.
     */
    public function getFactorAttribute(): string;
}
