<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Discountable
{
    /**
     * Return the discount relationship.
     */
    public function discount(): BelongsTo;

    /**
     * Return the discountable relationship.
     */
    public function discountable(): MorphTo;

    /**
     * Scope a query where type is condition.
     */
    public function scopeCondition(Builder $query): Builder;
}
