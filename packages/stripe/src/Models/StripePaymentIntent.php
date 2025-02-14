<?php

namespace Lunar\Stripe\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Base\BaseModel;
use Lunar\Models\Cart;

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
}
