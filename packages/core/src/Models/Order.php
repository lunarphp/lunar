<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\DiscountBreakdown;
use Lunar\Base\Casts\Price;
use Lunar\Base\Casts\ShippingBreakdown;
use Lunar\Base\Casts\TaxBreakdown;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasTags;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\Traits\Searchable;
use Lunar\Database\Factories\OrderFactory;

/**
 * @property int $id
 * @property ?int $customer_id
 * @property ?int $user_id
 * @property int $channel_id
 * @property bool $new_customer
 * @property string $status
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
 * @property ?\Illuminate\Support\Carbon $placed_at
 * @property ?array $meta
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Order extends BaseModel
{
    use HasFactory,
        HasMacros,
        HasTags,
        LogsActivity,
        Searchable;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'tax_breakdown' => TaxBreakdown::class,
        'meta' => AsArrayObject::class,
        'placed_at' => 'datetime',
        'sub_total' => Price::class,
        'discount_total' => Price::class,
        'discount_breakdown' => DiscountBreakdown::class,
        'shipping_breakdown' => ShippingBreakdown::class,
        'tax_total' => Price::class,
        'total' => Price::class,
        'shipping_total' => Price::class,
        'new_customer' => 'boolean',
    ];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    /**
     * Getter for status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $statuses = config('lunar.orders.statuses');

        return $statuses[$this->status]['label'] ?? $this->status;
    }

    /**
     * Return the channel relationship.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Return the cart relationship.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Return the lines relationship.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    /**
     * Return physical product lines relationship.
     */
    public function physicalLines(): HasMany
    {
        return $this->lines()->whereType('physical');
    }

    /**
     * Return digital product lines relationship.
     */
    public function digitalLines(): HasMany
    {
        return $this->lines()->whereType('digital');
    }

    /**
     * Return shipping lines relationship.
     */
    public function shippingLines(): HasMany
    {
        return $this->lines()->whereType('shipping');
    }

    /**
     * Return product lines relationship.
     */
    public function productLines(): HasMany
    {
        return $this->lines()->where('type', '!=', 'shipping');
    }

    /**
     * Return the currency relationship.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    /**
     * Return the addresses relationship.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class, 'order_id');
    }

    /**
     * Return the shipping address relationship.
     */
    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')->whereType('shipping');
    }

    /**
     * Return the billing address relationship.
     */
    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class, 'order_id')->whereType('billing');
    }

    /**
     * Return the transactions relationship.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->orderBy('created_at', 'desc');
    }

    /**
     * Return the charges relationship.
     */
    public function captures(): HasMany
    {
        return $this->transactions()->whereType('capture');
    }

    /**
     * Return the charges relationship.
     */
    public function intents(): HasMany
    {
        return $this->transactions()->whereType('intent');
    }

    /**
     * Return the refunds relationship.
     */
    public function refunds(): HasMany
    {
        return $this->transactions()->whereType('refund');
    }

    /**
     * Return the customer relationship.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Return the user relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model')
        );
    }

    /**
     * Determines if this is a draft order.
     */
    public function isDraft(): bool
    {
        return ! $this->isPlaced();
    }

    /**
     * Determines if this is a placed order.
     */
    public function isPlaced(): bool
    {
        return ! blank($this->placed_at);
    }

    public static function getDefaultLogExcept(): array
    {
        return [
            'status',
        ];
    }
}
