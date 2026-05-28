<?php

namespace Lunar\Core\Models;

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
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Database\Factories\OrderFactory;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasTags;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Concerns\Searchable;
use Lunar\Core\Models\Contracts\Currency as CurrencyContract;
use Lunar\Core\States\Order\FulfilmentState;
use Lunar\Core\States\Order\OrderState;
use Lunar\Core\States\Order\PaymentState;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property ?int $customer_id
 * @property ?int $user_id
 * @property int $channel_id
 * @property bool $new_customer
 * @property PaymentState $payment_status
 * @property FulfilmentState $fulfilment_status
 * @property OrderState $order_status
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
        'sub_total' => 'integer',
        'discount_total' => 'integer',
        'discount_breakdown' => DiscountBreakdown::class,
        'shipping_breakdown' => ShippingBreakdown::class,
        'tax_total' => 'integer',
        'total' => 'integer',
        'shipping_total' => 'integer',
        'new_customer' => 'boolean',
        'payment_status' => PaymentState::class,
        'fulfilment_status' => FulfilmentState::class,
        'order_status' => OrderState::class,
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

    public function computeOrderStatus(): void
    {
        if ($this->order_status->isManualOverride()) {
            return;
        }

        $config = app(OrderStateConfig::class);
        $newStateClass = $config->resolveOrderState($this->payment_status, $this->fulfilment_status);

        $previousValue = $this->getRawOriginal('order_status');
        $newValue = $newStateClass::getMorphClass();

        if ($previousValue === $newValue) {
            return;
        }

        // saveQuietly() bypasses the observer; syncOriginal() has not yet run
        // when updated() fires, so a nested save() would see payment_status /
        // fulfilment_status as still dirty and recurse indefinitely.
        $this->forceFill(['order_status' => $newStateClass]);
        $this->saveQuietly();

        OrderStatusUpdated::dispatch($this, $previousValue, $this->order_status->getValue());
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

    public function physicalLines(): HasMany
    {
        return $this->lines()->whereType('physical');
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

    public static function getDefaultLogExcept(): array
    {
        return [
            'payment_status',
            'fulfilment_status',
            'order_status',
        ];
    }
}
