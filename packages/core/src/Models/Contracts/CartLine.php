<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface CartLine
{
    /**
     * Return the cart relationship.
     */
    public function cart(): BelongsTo;

    /**
     * Return the tax class relationship.
     */
    public function taxClass(): HasOneThrough;

    /**
     * Return the cart line discount.
     */
    public function discounts(): BelongsToMany;

    /**
     * Return the polymorphic relation.
     */
    public function purchasable(): MorphTo;
}
