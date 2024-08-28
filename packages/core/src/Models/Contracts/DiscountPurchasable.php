<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface DiscountPurchasable
{
    /**
     * Return the discount relationship.
     */
    public function discount(): BelongsTo;

    /**
     * Return the priceable relationship.
     */
    public function purchasable(): MorphTo;

    /**
     * Scope a query where type is condition.
     */
    public function scopeCondition(Builder $query): Builder;
}
