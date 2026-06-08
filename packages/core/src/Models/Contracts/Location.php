<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Location
{
    /**
     * Return the fulfilments relationship.
     */
    public function fulfilments(): HasMany;
}
