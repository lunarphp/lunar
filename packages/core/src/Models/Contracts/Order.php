<?php

namespace Lunar\Core\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface Order
{
    /**
     * Return the channel relationship.
     */
    public function channel(): BelongsTo;

    /**
     * Return the cart relationship.
     */
    public function cart(): BelongsTo;

    /**
     * Return the lines relationship.
     */
    public function lines(): HasMany;

    /**
     * Return the fulfilments relationship.
     */
    public function fulfilments(): HasMany;

    /**
     * Return physical product lines relationship.
     */
    public function physicalLines(): HasMany;

    /**
     * Return digital product lines relationship.
     */
    public function digitalLines(): HasMany;

    /**
     * Return shipping lines relationship.
     */
    public function shippingLines(): HasMany;

    /**
     * Return product lines relationship.
     */
    public function productLines(): HasMany;

    /**
     * Return the currency relationship.
     */
    public function currency(): BelongsTo;

    /**
     * Return the addresses relationship.
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
     * Return the transactions relationship.
     */
    public function transactions(): HasMany;

    /**
     * Return the charges relationship.
     */
    public function captures(): HasMany;

    /**
     * Return the charges relationship.
     */
    public function intents(): HasMany;

    /**
     * Return the refunds relationship.
     */
    public function refunds(): HasMany;

    /**
     * Return the customer relationship.
     */
    public function customer(): BelongsTo;

    /**
     * Return the user relationship.
     */
    public function user(): BelongsTo;

    /**
     * Determines if this is a draft order.
     */
    public function isDraft(): bool;

    /**
     * Determines if this is a placed order.
     */
    public function isPlaced(): bool;

    /**
     * Whether the order is still open (not archived).
     */
    public function isOpen(): bool;

    /**
     * Whether the order has been closed (archived / dealt with).
     */
    public function isClosed(): bool;

    /**
     * Whether the order has been cancelled.
     */
    public function isCancelled(): bool;

    /**
     * The headline lifecycle key for display — cancelled takes precedence over
     * the open/closed archive state. Maps to `lunar::states.order.*`.
     *
     * @return 'cancelled'|'closed'|'open'
     */
    public function lifecycleStatus(): string;

    /**
     * The human-readable label for the cancellation reason, resolved from the
     * configured reason list (falls back to the stored key).
     */
    public function cancelReasonLabel(): ?string;
}
