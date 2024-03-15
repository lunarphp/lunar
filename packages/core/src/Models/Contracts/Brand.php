<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Brand
{
    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): MorphToMany;

    /**
     * Return the product relationship.
     */
    public function products(): HasMany;
}
