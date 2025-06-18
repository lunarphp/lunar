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
     * Return the discount's discountables relationship.
     */
    public function discountables(): HasMany;

    /**
     * Return the discount's discountable conditions relationship.
     */
    public function discountableConditions(): HasMany;

    /**
     * Return the discount's discountable exclusions relationship.
     */
    public function discountableExclusions(): HasMany;

    /**
     * Return the discount's discountable limitations relationship.
     */
    public function discountableLimitations(): HasMany;

    /**
     * Return the discount's discountable rewards relationship.
     */
    public function discountableRewards(): HasMany;

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
