<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface State
{
    /**
     * Return the country relationship.
     */
    public function country(): BelongsTo;
}
