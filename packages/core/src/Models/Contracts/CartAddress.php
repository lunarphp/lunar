<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface CartAddress
{
    /**
     * Return the cart relationship.
     */
    public function cart(): BelongsTo;

    /**
     * Return the country relationship.
     */
    public function country(): BelongsTo;
}
