<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Region
{
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
