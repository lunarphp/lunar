<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface CustomerGroup
{
    /**
     * Return the customer group's customers relationship.
     */
    public function customers(): BelongsToMany;

    /**
     * Return the customer group's products relationship.
     */
    public function products(): BelongsToMany;

    /**
     * Return the customer group's collections relationship.
     */
    public function collections(): BelongsToMany;
}
