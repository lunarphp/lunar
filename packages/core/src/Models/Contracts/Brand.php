<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Brand
{
    /**
     * Get the mapped attributes relation.
     */
    public function mappedAttributes(): HasMany;

    /**
     * Return the product relationship.
     */
    public function products(): HasMany;
}
