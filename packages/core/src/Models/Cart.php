<?php

namespace Lunar\Models;

use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Address;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\CartFactory;
use Lunar\DataTypes\Price;
use Lunar\Managers\CartManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Cart extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasMacros;

    /**
     * The cart total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public $total = null;

    /**
     * The cart sub total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public $subTotal = null;

    /**
     * The cart tax total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public $taxTotal = null;

    /**
     * The discount total.
     *
     * @var null|\Lunar\DataTypes\Price
     */
    public ?Price $discountTotal = null;

    /**
     * All the tax breakdowns for the cart.
     *
     * @var Collection
     */
    public Collection $taxBreakdown;

    /**
     * The shipping total for the cart.
     *
     * @var Price|null
     */
    public ?Price $shippingTotal = null;

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\CartFactory
     */
    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
        'meta'         => 'object',
    ];

    /**
     * Return the cart lines relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(CartLine::class, 'cart_id', 'id');
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Return the user relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopeUnmerged($query)
    {
        return $query->whereNull('merged_id');
    }

    /**
     * Return the addresses relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(CartAddress::class, 'cart_id');
    }

    /**
     * Return the shipping address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function shippingAddress()
    {
        return $this->hasOne(CartAddress::class, 'cart_id')->whereType('shipping');
    }

    /**
     * Return the billing address relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function billingAddress()
    {
        return $this->hasOne(CartAddress::class, 'cart_id')->whereType('billing');
    }

    /**
     * Return the order relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Return the cart manager.
     *
     * @return \Lunar\Managers\CartManager
     */
    public function getManager()
    {
        return new CartManager($this);
    }

    /**
     * Apply scope to get active cart.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereDoesntHave('order');
    }
}
