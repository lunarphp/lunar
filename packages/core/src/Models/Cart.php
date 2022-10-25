<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Address;
use Lunar\Base\Traits\CachesProperties;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\Cart\FreeItem;
use Lunar\Base\ValueObjects\Cart\Promotion;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Database\Factories\CartFactory;
use Lunar\DataTypes\Price;
use Lunar\Discounts\Models\DiscountReward;
use Lunar\Managers\CartManager;

class Cart extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use HasMacros;
    use CachesProperties;

    /**
     * Array of cachable class properties.
     *
     * @var array
     */
    public $cachableProperties = [
        'subTotal',
        'shippingTotal',
        'taxTotal',
        'cartDiscountAmount',
        'discountTotal',
        'total',
        'taxBreakdown',
        'promotions',
        'freeItems',
    ];

    /**
     * The cart manager.
     *
     * @var null|CartManager
     */
    protected ?CartManager $manager = null;

    /**
     * The cart sub total.
     * Sum of cart line amounts, before tax, shipping and cart-level discounts.
     *
     * @var null|Price
     */
    public ?Price $subTotal = null;

    public ?Collection $discounts = null;

    /**
     * The shipping total for the cart.
     *
     * @var null|Price
     */
    public ?Price $shippingTotal = null;

    /**
     * The cart tax total.
     * Sum of all tax to pay across cart lines and shipping.
     *
     * @var null|Price
     */
    public ?Price $taxTotal = null;

    /**
     * The cart discount amount.
     * Cart-level discount (ie. not cart-line discounts).
     *
     * @var null|Price
     */
    public ?Price $cartDiscountAmount = null;

    /**
     * The discount total.
     * Sum of all cart line discounts and cart-level discounts.
     *
     * @var null|Price
     */
    public ?Price $discountTotal = null;

    /**
     * The cart total.
     * Sum of the cart-line amounts, shipping and tax, minus cart-level discount amount.
     *
     * @var null|Price
     */
    public ?Price $total = null;

    /**
     * All the tax breakdowns for the cart.
     *
     * @var null|Collection<TaxBreakdown>
     */
    public ?Collection $taxBreakdown = null;

    /**
     * The cart-level promotions.
     *
     * @var null|Collection<Promotion>
     */
    public ?Collection $promotions = null;

    /**
     * Qualifying promotional free items.
     *
     * @var null|Collection<FreeItem>
     */
    public ?Collection $freeItems = null;

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
        'meta' => 'object',
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
        return $this->manager ?? new CartManager($this);
    }

    /**
     * Set the cart manager.
     *
     * @var \Lunar\Managers\CartManager
     */
    public function setManager(CartManager $manager)
    {
        $this->manager = $manager;
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

    public function addDiscount(DiscountReward $reward)
    {
        if (! $this->discounts) {
            $this->discounts = collect();
        }

        $this->discounts->push(
            $reward
        );

        return true;
    }
}
