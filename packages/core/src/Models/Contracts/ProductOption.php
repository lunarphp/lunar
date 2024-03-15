<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Models\ProductOptionValue;

interface ProductOption
{
    /**
     * Apply the shared scope.
     */
    public function scopeShared(Builder $builder): Builder;

    /**
     * Apply the exclusive scope.
     */
    public function scopeExclusive(Builder $builder): Builder;

    /**
     * Return the values relationship.
     *
     * @return HasMany<ProductOptionValue>
     */
    public function values(): HasMany;

    /**
     * Return the product's option products relationship.
     */
    public function products(): BelongsToMany;
}
