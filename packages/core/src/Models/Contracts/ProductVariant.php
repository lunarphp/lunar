<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface ProductVariant
{
    /**
     * The related product.
     */
    public function product(): BelongsTo;

    /**
     * Return the tax class relationship.
     */
    public function taxClass(): BelongsTo;

    /**
     * Return the related product option values.
     */
    public function values(): BelongsToMany;
}
