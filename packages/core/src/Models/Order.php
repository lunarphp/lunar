<?php

namespace Lunar\Models;

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
        Searchable,
        HasTags,
        LogsActivity,
        HasMacros;

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
        'tags',
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
        'tax_breakdown' => TaxBreakdown::class,
        'meta' => 'object',
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
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return config('scout.prefix').'orders';
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
     * {@inheritDoc}
     */
    public function getSearchableAttributes()
    {
        $data = [
            'id' => $this->id,
            'channel' => $this->channel->name,
            'reference' => $this->reference,
            'customer_reference' => $this->customer_reference,
            'status' => $this->status,
            'placed_at' => optional($this->placed_at)->timestamp,
            'created_at' => $this->created_at->timestamp,
            'sub_total' => $this->sub_total->value,
            'total' => $this->total->value,
            'currency_code' => $this->currency_code,
            'charges' => $this->transactions->map(function ($transaction) {
                return [
                    'reference' => $transaction->reference,
                ];
            }),
            'currency' => $this->currency_code,
            'lines' => $this->productLines->map(function ($line) {
                return [
                    'description' => $line->description,
                    'identifier' => $line->identifier,
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

        $data['tags'] = $this->tags->pluck('value')->toArray();

        return $data;
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
