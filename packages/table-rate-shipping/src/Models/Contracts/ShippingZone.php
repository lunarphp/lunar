<?php

namespace Lunar\Shipping\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface ShippingZone
{
    /**
     * Return the shipping methods relationship.
     */
    public function shippingMethods(): HasMany;

    /**
     * Return the countries relationship.
     */
    public function countries(): BelongsToMany;

    /**
     * Return the states relationship.
     */
    public function states(): BelongsToMany;

    /**
     * Return the postcodes relationship.
     */
    public function postcodes(): HasMany;

    public function rates(): HasMany;

    /**
     * Return the shipping exclusions property.
     */
    public function shippingExclusions(): BelongsToMany;
}
