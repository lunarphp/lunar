<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface ProductOptionValue
{
    /**
     * Return the option relationship.
     */
    public function option(): BelongsTo;

    /**
     * Return the option value's variants relationship.
     */
    public function variants(): BelongsToMany;
}
