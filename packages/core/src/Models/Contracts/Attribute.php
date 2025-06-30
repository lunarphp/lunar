<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Attribute
{
    /**
     * Return the attributable relation.
     */
    public function attributable(): MorphTo;

    /**
     * Returns the attribute group relation.
     */
    public function attributeGroup(): BelongsTo;

    /**
     * Apply the system scope to the query.
     */
    public function scopeSystem(Builder $query, $type): Builder;
}
