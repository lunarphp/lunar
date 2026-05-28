<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface AttributeModel
{
    /**
     * Return the attribute relation.
     */
    public function attribute(): BelongsTo;
}
