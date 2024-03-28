<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ProductAssociation
{
    /**
     * Return the parent relationship.
     */
    public function parent(): BelongsTo;

    /**
     * Return the parent relationship.
     */
    public function target(): BelongsTo;

    /**
     * Apply the cross-sell scope.
     */
    public function scopeCrossSell(Builder $query): Builder;

    /**
     * Apply the upsell scope.
     */
    public function scopeUpSell(Builder $query): Builder;

    /**
     * Apply the up alternate scope.
     */
    public function scopeAlternate(Builder $query): Builder;

    /**
     * Apply the type scope.
     */
    public function scopeType(Builder $query, string $type): Builder;
}
