<?php

namespace Lunar\Checkout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Checkout\Database\Factories\CheckoutSessionFactory;
use Lunar\Checkout\States\CheckoutSession\CheckoutSessionState;
use Lunar\Checkout\States\CheckoutSession\Expired;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Core\Models\Base;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Customer;
use Spatie\ModelStates\HasStates;

/**
 * A checkout attempt created from a cart. Carries a public UUID capability
 * token, a pinned snapshot of the cart's currency/channel/totals (the integrity
 * anchor — frozen at creation, never re-read), and its own lifecycle machine.
 *
 * @property int $id
 * @property string $uuid
 * @property int $cart_id
 * @property int $channel_id
 * @property string $currency_code
 * @property ?string $locale
 * @property int $amount_subtotal
 * @property int $amount_total
 * @property CheckoutSessionState $status
 * @property ?int $customer_id
 * @property ?string $customer_email
 * @property ?string $payment_intent_ref
 * @property ?string $client_reference_id
 * @property ?string $order_type
 * @property ?int $order_id
 * @property ?string $success_url
 * @property ?string $cancel_url
 * @property ?\ArrayObject $metadata
 * @property ?\ArrayObject $meta
 * @property ?Carbon $expires_at
 * @property ?Carbon $completed_at
 * @property ?Carbon $canceled_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class CheckoutSession extends Base
{
    use HasFactory;
    use HasStates;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'amount_subtotal' => 'integer',
        'amount_total' => 'integer',
        'status' => CheckoutSessionState::class,
        'metadata' => AsArrayObject::class,
        'meta' => AsArrayObject::class,
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Mint the public capability token. Unguessable, never derived from the
        // cart id, never sequential (spec 0004 §F).
        static::creating(function (CheckoutSession $session): void {
            if (empty($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }
        });
    }

    protected static function newFactory(): CheckoutSessionFactory
    {
        return CheckoutSessionFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::modelClass());
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::modelClass());
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::modelClass());
    }

    public function order(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The hosted-checkout URL — derived from the uuid, never stored.
     */
    public function getUrlAttribute(): string
    {
        return url(config('lunar.checkout.path', 'checkout').'/'.$this->uuid);
    }

    public function isExpired(): bool
    {
        if ($this->status instanceof Expired) {
            return true;
        }

        return $this->expires_at?->isPast() ?? false;
    }

    /**
     * Open sessions that have passed their expiry window — the set the expiry
     * job transitions to {@see Expired}.
     */
    public function scopeStale(Builder $query): Builder
    {
        return $query->where('status', Open::$name)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }
}
