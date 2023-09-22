<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property string $currency
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
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $statuses = config('lunar.orders.statuses');

        return $statuses[$this->status]['label'] ?? $this->status;
    }

    /**
     * Return the channel relationship.
     *
     * @return void
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Return the cart relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Return the lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    /**
     * Return physical product lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function physicalLines()
    {
        return $this->lines()->whereType('physical');
    }

    /**
     * Return digital product lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function digitalLines()
    {
        return $this->lines()->whereType('digital');
    }

    /**
     * Return shipping lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shippingLines()
    {
        return $this->lines()->whereType('shipping');
    }

    /**
     * Return product lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productLines()
    {
        return $this->lines()->where('type', '!=', 'shipping');
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    /**
     * Return the addresses relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(OrderAddress::class, 'order_id');
    }

    /**
     * Return the shipping address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shippingAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id')->whereType('shipping');
    }

    /**
     * Return the billing address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function billingAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id')->whereType('billing');
    }

    /**
     * Return the transactions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Return the charges relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function captures()
    {
        return $this->transactions()->whereType('capture');
    }

    /**
     * Return the charges relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function intents()
    {
        return $this->transactions()->whereType('intent');
    }

    /**
     * Return the refunds relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refunds()
    {
        return $this->transactions()->whereType('refund');
    }

    /**
     * Return the customer relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Return the user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
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
}
