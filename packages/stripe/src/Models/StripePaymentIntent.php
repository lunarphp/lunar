<?php

namespace Lunar\Stripe\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Models\Cart;
use Stripe\PaymentIntent;

class StripePaymentIntent extends BaseModel
{
    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [PaymentIntent::STATUS_CANCELED, PaymentIntent::STATUS_SUCCEEDED]);
    }
}
