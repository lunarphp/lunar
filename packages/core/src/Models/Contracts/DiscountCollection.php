<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface DiscountCollection
{
    /**
     * Return the discount relationship.
     */
    public function discount(): BelongsTo;

    /**
     * Return the collection relationship.
     */
    public function collection(): BelongsTo;
}
