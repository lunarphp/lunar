<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Region
{
    /**
     * Whether the storefront shows prices inclusive of tax in this region
     * (a display preference; it does not affect how prices are stored).
     */
    public function displaysPricesIncludingTax(): bool;

    /**
     * Return the channel relationship.
     */
    public function channel(): BelongsTo;

    /**
     * Return the currency relationship.
     */
    public function currency(): BelongsTo;

    /**
     * Return the language relationship.
     */
    public function language(): BelongsTo;

    /**
     * Return the display tax zone relationship.
     */
    public function taxZone(): BelongsTo;

    /**
     * Return the countries this region serves.
     */
    public function countries(): BelongsToMany;
}
