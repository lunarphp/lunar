<?php

namespace Lunar\Models\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Lunar\Base\Addressable;
use Lunar\Base\LunarUser;
use Lunar\Base\Purchasable;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\FingerprintMismatchException;
use Lunar\Models\Customer;
use Lunar\Models\Order;

interface Cart
{
    /**
     * Return the cart lines relationship.
     */
    public function lines(): HasMany;

    /**
     * Return the currency relationship.
     */
    public function currency(): BelongsTo;

    /**
     * Return the user relationship.
     */
    public function user(): BelongsTo;

    /**
     * Return the customer relationship.
     */
    public function customer(): BelongsTo;

    /**
     * Apply the unmerged scope to the query.
     */
    public function scopeUnmerged(Builder $query): Builder;

    /**
     * Return the cart's addresses relationship.
     */
    public function addresses(): HasMany;

    /**
     * Return the shipping address relationship.
     */
    public function shippingAddress(): HasOne;

    /**
     * Return the billing address relationship.
     */
    public function billingAddress(): HasOne;

    /**
     * Return the order relationship.
     */
    public function orders(): HasMany;

    /**
     * Apply scope to get active cart.
     */
    public function scopeActive(Builder $query): Builder;

    /**
     * Return the draft order relationship.
     */
    public function draftOrder(?int $draftOrderId = null): HasOne;

    /**
     * Return the completed order relationship.
     */
    public function completedOrder(?int $completedOrderId = null): HasOne;

    /**
     * Return the carts completed order.
     */
    public function completedOrders(): HasMany;

    /**
     * Return whether the cart has any completed order.
     */
    public function hasCompletedOrders(): bool;

    /**
     * Calculate the cart totals and cache the result.
     */
    public function calculate(): \Lunar\Models\Cart;

    /**
     * Add or update a purchasable item to the cart
     */
    public function add(Purchasable $purchasable, int $quantity = 1, array $meta = [], bool $refresh = true): \Lunar\Models\Cart;

    /**
     * Add cart lines.
     */
    public function addLines(iterable $lines): \Lunar\Models\Cart;

    /**
     * Remove a cart line
     */
    public function remove(int $cartLineId, bool $refresh = true): \Lunar\Models\Cart;

    /**
     * Update cart line
     */
    public function updateLine(int $cartLineId, int $quantity, ?array $meta = null, bool $refresh = true): \Lunar\Models\Cart;

    /**
     * Update cart lines.
     */
    public function updateLines(Collection $lines): \Lunar\Models\Cart;

    /**
     * Deletes all cart lines.
     */
    public function clear(): \Lunar\Models\Cart;

    /**
     * Associate a user to the cart
     */
    public function associate(LunarUser $user, string $policy = 'merge', bool $refresh = true): \Lunar\Models\Cart;

    /**
     * Associate a customer to the cart
     */
    public function setCustomer(Customer $customer): \Lunar\Models\Cart;

    /**
     * Add an address to the Cart.
     */
    public function addAddress(array|Addressable $address, string $type, bool $refresh = true): \Lunar\Models\Cart;

    /**
     * Set the shipping address.
     */
    public function setShippingAddress(array|Addressable $address): \Lunar\Models\Cart;

    /**
     * Set the billing address.
     */
    public function setBillingAddress(array|Addressable $address): \Lunar\Models\Cart;

    /**
     * Set the shipping option to the shipping address.
     */
    public function setShippingOption(ShippingOption $option, bool $refresh = true): \Lunar\Models\Cart;

    /**
     * Get the shipping option for the cart
     */
    public function getShippingOption(): ?ShippingOption;

    /**
     * Returns whether the cart has shippable items.
     */
    public function isShippable(): bool;

    /**
     * Create an order from the Cart.
     */
    public function createOrder(bool $allowMultipleOrders = false, ?int $orderIdToUpdate = null): Order;

    /**
     * Returns whether a cart has enough info to create an order.
     */
    public function canCreateOrder(): bool;

    /**
     * Get a unique fingerprint for the cart to identify if the contents have changed.
     */
    public function fingerprint(): string;

    /**
     * Check whether a given fingerprint matches the one being generated for the cart.
     *
     * @throws FingerprintMismatchException
     */
    public function checkFingerprint(string $fingerprint): bool;

    /**
     * Return the estimated shipping cost for a cart.
     */
    public function getEstimatedShipping(array $params, bool $setOverride = false): ?ShippingOption;
}
