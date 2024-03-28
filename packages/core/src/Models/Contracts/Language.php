<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Language
{
    /**
     * Return the URLs relationship
     */
    public function urls(): HasMany;
}
