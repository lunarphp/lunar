<?php

namespace Lunar\Paypal\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;

/**
 * One row per PayPal order the driver has seen. Gives authorize() somewhere to
 * record that it is mid-flight so a replayed request cannot process the same
 * PayPal order twice, and gives webhooks a way to resolve an inbound PayPal
 * order ID back to a cart or order.
 *
 * @property string $paypal_order_id
 * @property string|null $status
 */
class PaypalOrder extends Base
{
    /**
     * States PayPal will not move an order out of.
     */
    const FINAL_STATES = ['COMPLETED', 'VOIDED'];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'processing_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('processed_at');
    }

    /**
     * Whether this PayPal order has already been carried through to a placed
     * order, and so must not be processed again.
     */
    public function isProcessed(): bool
    {
        return (bool) $this->processed_at;
    }
}
