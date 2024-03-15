<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface CollectionGroup
{
    /**
     * Return the collection group collections relationship.
     */
    public function collections(): HasMany;
}
