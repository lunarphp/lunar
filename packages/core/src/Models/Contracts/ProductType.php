<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface ProductType
{
    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): BelongsToMany;

    /**
     * Return the product attributes relationship.
     */
    public function productAttributes(): BelongsToMany;

    /**
     * Return the variant attributes relationship.
     */
    public function variantAttributes(): BelongsToMany;

    /**
     * Get the products relation.
     */
    public function products(): HasMany;
}
