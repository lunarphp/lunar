<?php

namespace Lunar\Checkout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Checkout\Database\Factories\CheckoutSessionFactory;
use Lunar\Checkout\States\CheckoutSession\CheckoutSessionState;
use Lunar\Checkout\States\CheckoutSession\Expired;
use Lunar\Checkout\States\CheckoutSession\Open;
use Lunar\Checkout\States\CheckoutSession\PaymentProcessing;
use Lunar\Core\Models\Base;
use Spatie\ModelStates\HasStates;

/**
 * A checkout attempt created from a cart. Backend-neutral: the cart, channel,
 * customer and resulting order are referenced by driver-opaque strings — zero
 * Lunar FKs (spec 0010 §A). While `Open` the session mirrors the live cart by
 * fingerprint; the amounts + fingerprint are pinned at the pay boundary and
 * re-verified at completion (spec 0010 §D/§E/§E.2).
 *
 * @property int $id
 * @property string $uuid
 * @property string $channel_handle
 * @property string $currency_code
 * @property ?string $locale
 * @property string $cart_reference
 * @property ?string $active_cart_reference
 * @property string $cart_fingerprint
 * @property int $amount_subtotal
 * @property int $amount_total
 * @property CheckoutSessionState $status
 * @property ?string $customer_reference
 * @property ?string $customer_email
 * @property ?string $payment_intent_ref
 * @property ?string $client_reference_id
 * @property ?string $order_reference
 * @property ?string $success_url
 * @property ?string $cancel_url
 * @property ?\ArrayObject $element_data
 * @property ?\ArrayObject $metadata
 * @property ?\ArrayObject $meta
 * @property int $reconciliation_attempts
 * @property ?Carbon $payment_processing_at
 * @property ?Carbon $expires_at
 * @property ?Carbon $completed_at
 * @property ?Carbon $cancelled_at
 * @property ?Carbon $pruned_at
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
        'element_data' => AsArrayObject::class,
        'metadata' => AsArrayObject::class,
        'meta' => AsArrayObject::class,
        'reconciliation_attempts' => 'integer',
        'payment_processing_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'pruned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Mint the public capability token. Unguessable, never derived from the
        // cart reference, never sequential (spec 0004 §F).
        static::creating(function (CheckoutSession $session): void {
            if (empty($session->uuid)) {
                $session->uuid = (string) Str::uuid();
            }

            // Mirror cart_reference into the unique active column for sessions
            // created in (or defaulting to) Open — the §F.2 concurrency anchor.
            $rawStatus = $session->getAttributes()['status'] ?? Open::$name;

            if ($session->active_cart_reference === null && in_array($rawStatus, [Open::$name, PaymentProcessing::$name], true)) {
                $session->active_cart_reference = $session->cart_reference;
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
     * The element bag (spec 0010 §C): price-neutral custom element data, keyed
     * by element handle.
     *
     * The whole bag is one JSON column, so a read-modify-write races: two
     * requests capturing different handles would each save a blob built from
     * their own stale read, and one handle would vanish. The row lock is what
     * makes concurrent writes to different handles both survive.
     *
     * NB `lockForUpdate()` is a no-op on SQLite (the test default) and only
     * takes effect on MySQL (production), so the test suite proves the
     * re-read-inside-transaction behaviour, not the row lock itself.
     */
    public function putElementData(string $handle, array $data): void
    {
        $this->getConnection()->transaction(function () use ($handle, $data): void {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $bag = $locked->element_data?->getArrayCopy() ?? [];
            $bag[$handle] = $data;

            $locked->element_data = $bag;
            $locked->save();

            // Mirror onto $this so a caller reading straight off this instance
            // sees the merged bag, but sync the original too: an unsynced
            // attribute leaves $this dirty, and a later unrelated save() on
            // this instance would rewrite the whole column from this stale
            // snapshot, outside the lock we just took.
            $this->element_data = $bag;
            $this->syncOriginalAttribute('element_data');
        });
    }

    public function forgetElementData(string $handle): void
    {
        $this->getConnection()->transaction(function () use ($handle): void {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $bag = $locked->element_data?->getArrayCopy() ?? [];
            unset($bag[$handle]);

            $locked->element_data = $bag;
            $locked->save();

            $this->element_data = $bag;
            $this->syncOriginalAttribute('element_data');
        });
    }

    public function getElementData(string $handle): ?array
    {
        return $this->element_data[$handle] ?? null;
    }

    /**
     * Concurrency-safe state transition (spec 0004 §C): the spatie machine is
     * the declarative legality layer; this guarded single-statement UPDATE is
     * the concurrency layer. Zero affected rows means another writer won the
     * race — the caller re-reads and reacts (409 / return existing / no-op).
     *
     * @param  list<string>  $fromStates  allowed source state names
     * @param  array<string, mixed>  $attributes  written atomically with the transition
     */
    public function transitionGuarded(array $fromStates, string $toState, array $attributes = []): bool
    {
        $updated = static::query()
            ->whereKey($this->getKey())
            ->whereIn('status', $fromStates)
            ->update([
                'status' => $toState,
                'updated_at' => now(),
                ...$attributes,
            ]);

        if ($updated === 1) {
            $this->refresh();

            return true;
        }

        $this->refresh();

        return false;
    }

    /**
     * Open sessions past their expiry window — the set the expiry command
     * transitions to {@see Expired} (via the guarded transition, never a blind
     * `transitionTo`).
     */
    public function scopeExpirable(Builder $query): Builder
    {
        return $query->where('status', Open::$name)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * PaymentProcessing sessions old enough for the bounded reconciliation
     * sweep (spec 0010 §F), aged on payment_processing_at.
     */
    public function scopeReconcilable(Builder $query, ?int $minutes = null): Builder
    {
        $minutes ??= (int) config('lunar.checkout.reconciliation.after_minutes', 60);

        return $query->where('status', PaymentProcessing::$name)
            ->whereNotNull('payment_processing_at')
            ->where('payment_processing_at', '<=', now()->subMinutes($minutes));
    }
}
