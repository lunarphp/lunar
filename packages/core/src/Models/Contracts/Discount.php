<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\DiscountTypes\AbstractDiscountType;

interface Discount
{
    /**
     * Return the discount's users relationship.
     */
    public function users(): BelongsToMany;

    /**
     * Return the discount's purchasables relationship.
     */
    public function purchasables(): HasMany;

    /**
     * Return the discount's purchasable conditions relationship.
     */
    public function purchasableConditions(): HasMany;

    /**
     * Return the discount's purchasable exclusions relationship.
     */
    public function purchasableExclusions(): HasMany;

    /**
     * Return the discount's purchasable limitations relationship.
     */
    public function purchasableLimitations(): HasMany;

    /**
     * Return the discount's purchasable rewards relationship.
     */
    public function purchasableRewards(): HasMany;

    /**
     * Return the discount's type class.
     */
    public function getType(): AbstractDiscountType;

    /**
     * Return the discount's collections relationship.
     */
    public function collections(): BelongsToMany;

    /**
     * Return the discount's customer groups relationship.
     */
    public function customerGroups(): BelongsToMany;

    /**
     * Return the discount's brands relationship.
     */
    public function brands(): BelongsToMany;

    /**
     * Return the active scope.
     */
    public function scopeActive(Builder $query): Builder;

    /**
     * Return the products scope.
     */
    public function scopeProducts(Builder $query, iterable $productIds = [], array|string $types = []): Builder;

    /**
     * Return the product variants scope.
     */
    public function scopeProductVariants(Builder $query, iterable $variantIds = [], array|string $types = []): Builder;

    /**
     * Return when the discount is usable.
     */
    public function scopeUsable(Builder $query): Builder;
}
