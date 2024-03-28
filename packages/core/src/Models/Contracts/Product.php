<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Product
{
    /**
     * Returns the attributes to be stored against this model.
     */
    public function mappedAttributes(): Collection;

    /**
     * Return the product type relation.
     */
    public function productType(): BelongsTo;

    /**
     * Return the product images relation.
     */
    public function images(): MorphMany;

    /**
     * Return the product variants relation.
     */
    public function variants(): HasMany;

    /**
     * Return the product collections relation.
     */
    public function collections(): BelongsToMany;

    /**
     * Return the product's associations relationship.
     */
    public function associations(): HasMany;

    /**
     * Return the product's associations relationship.
     */
    public function inverseAssociations(): HasMany;

    /**
     * Associate a product to another with a type.
     */
    public function associate(mixed $product, string $type): void;

    /**
     * Dissociate a product to another with a type.
     */
    public function dissociate(mixed $product, string $type = null): void;

    /**
     * Return the customer groups relationship.
     */
    public function customerGroups(): BelongsToMany;

    /**
     * Return the brand relationship.
     */
    public function brand(): BelongsTo;

    /**
     * Apply the status scope.
     */
    public function scopeStatus(Builder $query, string $status): Builder;

    /**
     * Return the product's prices relationship.
     */
    public function prices(): HasManyThrough;

    /**
     * Return the product options relationship.
     */
    public function productOptions(): BelongsToMany;
}
