<?php

namespace Lunar\Stripe\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Models\Cart;
use Stripe\PaymentIntent;

class StripePaymentIntent extends BaseModel
{
    const FINAL_STATES = [PaymentIntent::STATUS_CANCELED, PaymentIntent::STATUS_SUCCEEDED];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::modelClass(), 'cart_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::FINAL_STATES);
    }

    public function isActive(): bool
    {
        return $this->status && ! in_array($this->status, self::FINAL_STATES);
    }
}
