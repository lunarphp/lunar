<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Core\Contracts\FieldType;

interface Attribute
{
    /**
     * Return the attribute group relation.
     */
    public function group(): BelongsTo;

    /**
     * Return the model-type applicability relation.
     */
    public function models(): HasMany;

    /**
     * Resolve the field type instance for this attribute.
     */
    public function fieldType(): FieldType;

    /**
     * Scope the query to system attributes.
     */
    public function scopeSystem(Builder $query): Builder;
}
