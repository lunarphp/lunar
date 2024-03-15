<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Customer
{
    /**
     * Return the customer group relationship.
     */
    public function customerGroups(): BelongsToMany;

    /**
     * Return the customer group relationship.
     */
    public function users(): BelongsToMany;

    /**
     * Return the customer's addresses relationship.
     */
    public function addresses(): HasMany;

    /**
     * Return the customer's orders relationship.
     */
    public function orders(): HasMany;

    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): MorphToMany;
}
