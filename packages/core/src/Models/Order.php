<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\Price;
use GetCandy\Base\Casts\TaxBreakdown;
use GetCandy\Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Order extends BaseModel
{
    use HasFactory;
    use Searchable;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'tax_breakdown'  => TaxBreakdown::class,
        'meta'           => 'object',
        'placed_at'      => 'datetime',
        'sub_total'      => Price::class,
        'discount_total' => Price::class,
        'tax_total'      => Price::class,
        'total'          => Price::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\OrderFactory
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
        $statuses = config('getcandy.orders.statuses');

        return $statuses[$this->status] ?? $this->status;
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
    public function charges()
    {
        return $this->transactions()->whereRefund(false);
    }

    /**
     * Return the refunds relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refunds()
    {
        return $this->transactions()->whereRefund(true);
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

    public function toSearchableArray()
    {
        if (config('scout.driver') == 'mysql') {
            return $this->only(array_keys($this->getAttributes()));
        }

        return [
            'id'        => $this->id,
            'reference' => $this->reference,
            'status'    => $this->status,
            'charges'   => $this->transactions->map(function ($transaction) {
                return [
                    'reference' => $transaction->reference,
                ];
            }),
            'addresses' => $this->addresses->map(function ($address) {
                return [
                    'postcode'   => $address->postcode,
                    'first_name' => $address->first_name,
                    'last_name'  => $address->last_name,
                ];
            })->toArray(),
            'currency' => $this->currency_code,
            'lines'    => $this->productLines->map(function ($line) {
                return [
                    'description' => $line->description,
                    'identifier'  => $line->identifier,
                ];
            })->toArray(),
        ];
    }
}
