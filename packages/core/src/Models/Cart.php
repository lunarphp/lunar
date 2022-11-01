<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Actions\Carts\AddOrUpdatePurchasable;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Address;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\CachesProperties;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\Cart\FreeItem;
use Lunar\Base\ValueObjects\Cart\Promotion;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Database\Factories\CartFactory;
use Lunar\DataTypes\Price;
use Lunar\Managers\CartManager;
use Lunar\Pipelines\Cart\Calculate;

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
     * Apply scope to get active cart.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereDoesntHave('order');
    }

    /**
     * Calculate the cart totals and cache the result.
     *
     * @return Cart
     */
    public function calculate(): Cart
    {
        return app(Pipeline::class)
        ->send($this)
        ->through(
            config('lunar.cart.pipelines.cart', [
                Calculate::class,
            ])
        )->thenReturn(function ($cart) {
            $cart->cacheProperties();

            return $cart;
        });
    }

    /**
     * Add or update a purchasable item to the cart
     *
     * @param  Purchasable  $purchasable
     * @param  int  $quantity
     * @param  array  $meta
     * @return Cart
     */
    public function add(Purchasable $purchasable, $quantity = 1, $meta = []): Cart
    {
        foreach (config('lunar.cart.validators.cart_lines', []) as $action) {
            // Throws a validation exception?
            app($action)->validate(
                cart: $this,
                purchasable: $purchasable,
                quantity: $quantity,
                meta: $meta
            );
        }

        return app(
            config('lunar.cart.actions.add_to_cart', AddOrUpdatePurchasable::class)
        )->execute($this, $purchasable, $quantity, $meta)
            ->then(fn () => $this->refresh()->calculate());
    }

    public function remove($cartLineId)
    {
        return app(
            config('lunar.cart.actions.remove_from_cart', AddOrUpdatePurchasable::class)
        )->execute($this, $cartLineId)
            ->then(fn () => $this->refresh()->calculate());
    }
}
