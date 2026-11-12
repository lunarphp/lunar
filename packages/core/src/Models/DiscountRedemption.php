<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\DiscountRedemptionFactory;

/**
 * One discount redeemed by one placed order.
 *
 * @property int $id
 * @property int $discount_id
 * @property ?int $coupon_id
 * @property ?int $order_id
 * @property ?int $cart_id
 * @property int|string|null $user_id
 * @property ?string $email
 * @property Carbon $redeemed_at
 */
class DiscountRedemption extends Base
{
    use HasFactory;

    /**
     * {@inheritDoc}
     */
    public $timestamps = false;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return DiscountRedemptionFactory::new();
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
