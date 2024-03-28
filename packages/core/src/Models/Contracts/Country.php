<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Country
{
    /**
     * Return the country's states relationship.
     */
    public function states(): HasMany;
}
