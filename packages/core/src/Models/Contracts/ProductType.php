<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface ProductType
{
    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): MorphToMany;

    /**
     * Return the product attributes relationship.
     */
    public function productAttributes(): MorphToMany;

    /**
     * Return the variant attributes relationship.
     */
    public function variantAttributes(): MorphToMany;

    /**
     * Get the products relation.
     */
    public function products(): HasMany;
}
