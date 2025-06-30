<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Collection
{
    /**
     * Return the group relationship.
     */
    public function group(): BelongsTo;

    /**
     * Apply the in group scope to the query builder.
     */
    public function scopeInGroup(Builder $builder, int $id): Builder;

    /**
     * Return the collection's products relationship.
     */
    public function products(): BelongsToMany;

    /**
     * Get the translated name of ancestor collections.
     */
    public function getBreadcrumbAttribute(): \Illuminate\Support\Collection;

    /**
     * Return the customer groups relationship.
     */
    public function customerGroups(): BelongsToMany;

    /**
     * Return the collection discounts relationship.
     */
    public function discounts(): BelongsToMany;
}
