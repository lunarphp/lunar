<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Lunar\Core\Casts\DiscountBreakdown;
use Lunar\Core\Casts\ShippingBreakdown;
use Lunar\Core\Casts\TaxBreakdown;
use Lunar\Core\Contracts\Actions\Fulfilment\CreatesFulfilment;
use Lunar\Core\Contracts\Actions\Orders\CancelsOrder;
use Lunar\Core\Contracts\Actions\Orders\CapturesOrder;
use Lunar\Core\Contracts\Actions\Orders\ClosesOrder;
use Lunar\Core\Contracts\Actions\Orders\RefundsOrder;
use Lunar\Core\Contracts\Actions\Orders\ReopensOrder;
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Database\Factories\OrderFactory;
use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasTags;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Core\Models\Contracts\Currency as CurrencyContract;
use Lunar\Core\States\Order\Fulfilment\FulfilmentStatus;
use Lunar\Core\States\Order\Payment\PaymentState;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property ?int $customer_id
 * @property ?int $user_id
 * @property int $channel_id
 * @property bool $new_customer
 * @property PaymentState $payment_status
 * @property FulfilmentStatus $fulfilment_status
 * @property ?string $reference
 * @property ?string $customer_reference
 * @property int $sub_total
 * @property int $discount_total
 * @property array $discount_breakdown
 * @property array $shipping_breakdown
 * @property array $tax_breakdown
 * @property int $tax_total
 * @property int $total
 * @property ?string $notes
 * @property string $currency_code
 * @property ?string $compare_currency_code
 * @property float $exchange_rate
 * @property ?Carbon $placed_at
 * @property ?Carbon $closed_at
 * @property ?Carbon $cancelled_at
 * @property ?string $cancel_reason
 * @property ?string $cancel_note
 * @property ?array $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Order extends Base implements Contracts\Order, HasCurrency
{
    use FormatsPrices;
    use HasFactory;
    use HasMacros;
    use HasStates;
    use HasTags;
    use LogsActivity;
    use Notifiable;
    use Searchable;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'tax_breakdown' => TaxBreakdown::class,
        'meta' => AsArrayObject::class,
        'placed_at' => 'datetime',
        'closed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'sub_total' => 'integer',
        'discount_total' => 'integer',
        'discount_breakdown' => DiscountBreakdown::class,
        'shipping_breakdown' => ShippingBreakdown::class,
        'tax_total' => 'integer',
        'total' => 'integer',
        'shipping_total' => 'integer',
        'new_customer' => 'boolean',
        'payment_status' => PaymentState::class,
        'fulfilment_status' => FulfilmentStatus::class,
    ];

    public function resolveCurrency(): CurrencyContract
    {
        $this->loadMissing('currency');

        return $this->currency ?? Currency::getDefault();
    }

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    protected static function newFactory()
    {
        return OrderFactory::new();
    }

    /**
     * Whether the order is still open (not archived).
     */
    public function isOpen(): bool
    {
        return blank($this->closed_at);
    }

    /**
     * Whether the order has been closed (archived / dealt with).
     */
    public function isClosed(): bool
    {
        return ! $this->isOpen();
    }

    /**
     * Limit the query to open (un-archived) orders.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('closed_at');
    }

    /**
     * Limit the query to closed (archived) orders.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereNotNull('closed_at');
    }

    /**
     * Whether the order has been cancelled.
     */
    public function isCancelled(): bool
    {
        return ! blank($this->cancelled_at);
    }

    /**
     * The headline lifecycle key for display — cancelled takes precedence over
     * the open/closed archive state. Maps to `lunar::states.order.*`.
     *
     * @return 'cancelled'|'closed'|'open'
     */
    public function lifecycleStatus(): string
    {
        return match (true) {
            $this->isCancelled() => 'cancelled',
            $this->isClosed() => 'closed',
            default => 'open',
        };
    }

    /**
     * Limit the query to cancelled orders.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->whereNotNull('cancelled_at');
    }

    /**
     * The human-readable label for the cancellation reason, resolved from the
     * configured reason list (falls back to the stored key).
     */
    public function cancelReasonLabel(): ?string
    {
        if (blank($this->cancel_reason)) {
            return null;
        }

        return config('lunar.orders.cancel_reasons.'.$this->cancel_reason, $this->cancel_reason);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::modelClass());
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::modelClass());
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::modelClass());
    }

    public function fulfilments(): HasMany
    {
        return $this->hasMany(Fulfilment::modelClass());
    }

    public function physicalLines(): HasMany
    {
        return $this->lines()->whereType('physical');
    }

    /**
     * Lines that need a fulfilment — stamped from the purchasable's
     * `requiresFulfilment()` at order creation. The fulfilment rollup keys off
     * this (a superset of `requires_shipping`: physical goods plus any that
     * need provisioning), not the open-set `type` string.
     */
    public function fulfillableLines(): HasMany
    {
        return $this->lines()->where('requires_fulfilment', true);
    }

    public function digitalLines(): HasMany
    {
        return $this->lines()->whereType('digital');
    }

    public function shippingLines(): HasMany
    {
        return $this->lines()->whereType('shipping');
    }

    public function productLines(): HasMany
    {
        return $this->lines()->where('type', '!=', 'shipping');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::modelClass(), 'currency_code', 'code');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::modelClass(), 'order_id');
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::modelClass(), 'order_id')->whereType('shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::modelClass(), 'order_id')->whereType('billing');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::modelClass())->orderBy('created_at', 'desc');
    }

    public function captures(): HasMany
    {
        return $this->transactions()->whereType('capture');
    }

    public function intents(): HasMany
    {
        return $this->transactions()->whereType('intent');
    }

    public function refunds(): HasMany
    {
        return $this->transactions()->whereType('refund');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::modelClass());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model')
        );
    }

    public function isDraft(): bool
    {
        return ! $this->isPlaced();
    }

    public function isPlaced(): bool
    {
        return ! blank($this->placed_at);
    }

    /**
     * Create a fulfilment covering specific order lines.
     *
     * @param  array<int|string, int>  $lines  [order_line_id => quantity]
     */
    public function createFulfilment(array $lines, array $attributes = []): Fulfilment
    {
        return app(CreatesFulfilment::class)->execute($this, $lines, $attributes);
    }

    /**
     * Cancel the order (unfulfilled orders only).
     */
    public function cancel(?string $reason = null, ?string $note = null, bool $notify = true): Order
    {
        return app(CancelsOrder::class)->execute($this, $reason, $note, $notify);
    }

    /**
     * Close (archive) the order.
     */
    public function close(): Order
    {
        return app(ClosesOrder::class)->execute($this);
    }

    /**
     * Reopen a closed order.
     */
    public function reopen(): Order
    {
        return app(ReopensOrder::class)->execute($this);
    }

    /**
     * Capture an amount against a successful payment intent transaction.
     */
    public function capture(int|string $transactionId, float|int|string $amount): PaymentCapture
    {
        return app(CapturesOrder::class)->execute($this, $transactionId, $amount);
    }

    /**
     * Refund an amount against a captured transaction.
     */
    public function refund(int|string $transactionId, float|int|string $amount, ?string $notes = null): PaymentRefund
    {
        return app(RefundsOrder::class)->execute($this, $transactionId, $amount, $notes);
    }

    public static function getDefaultLogExcept(): array
    {
        return [
            'payment_status',
            'fulfilment_status',
        ];
    }
}
