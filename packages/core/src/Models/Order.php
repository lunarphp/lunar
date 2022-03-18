<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\Price;
use GetCandy\Base\Casts\TaxBreakdown;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Base\Traits\Searchable;
use GetCandy\Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory,
        Searchable,
        LogsActivity;

    /**
     * Define our base filterable attributes.
     *
     * @var array
     */
    protected $filterable = [
        '__soft_deleted',
        'status',
        'created_at',
        'placed_at',
    ];

    /**
     * Define our base sortable attributes.
     *
     * @var array
     */
    protected $sortable = [
        'created_at',
        'placed_at',
        'total',
    ];

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
        'shipping_total' => Price::class,
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

        return $statuses[$this->status]['label'] ?? $this->status;
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
     * Return the channel relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
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
     * {@inheritDoc}
     */
    protected function getSearchableAttributes()
    {
        $data = [
            'id'        => $this->id,
            'channel'    => $this->channel->name,
            'reference' => $this->reference,
            'customer_reference' => $this->customer_reference,
            'status'    => $this->status,
            'placed_at' => optional($this->placed_at)->timestamp,
            'created_at' => $this->created_at->timestamp,
            'sub_total' => $this->sub_total->value,
            'total'     => $this->total->value,
            'currency_code'  => $this->currency_code,
            'charges'   => $this->transactions->map(function ($transaction) {
                return [
                    'reference' => $transaction->reference,
                ];
            }),
            'currency' => $this->currency_code,
            'lines'    => $this->productLines->map(function ($line) {
                return [
                    'description' => $line->description,
                    'identifier'  => $line->identifier,
                ];
            })->toArray(),
        ];

        foreach ($this->addresses as $address) {
            $fields = [
                'first_name',
                'last_name',
                'company_name',
                'line_one',
                'line_two',
                'line_three',
                'city',
                'state',
                'postcode',
                'contact_email',
                'contact_phone',
            ];

            foreach ($fields as $field) {
                $data["{$address->type}_{$field}"] = $address->getAttribute($field);
            }

            $data["{$address->type}_country"] = optional($address->country)->name;
        }

        return $data;
    }
}
