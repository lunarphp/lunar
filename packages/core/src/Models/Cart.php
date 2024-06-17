<?php

namespace Lunar\Models;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Lunar\Actions\Carts\AddAddress;
use Lunar\Actions\Carts\AddOrUpdatePurchasable;
use Lunar\Actions\Carts\AssociateUser;
use Lunar\Actions\Carts\CreateOrder;
use Lunar\Actions\Carts\GenerateFingerprint;
use Lunar\Actions\Carts\RemovePurchasable;
use Lunar\Actions\Carts\SetShippingOption;
use Lunar\Actions\Carts\UpdateCartLine;
use Lunar\Base\Addressable;
use Lunar\Base\BaseModel;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\CachesProperties;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Base\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Base\ValueObjects\Cart\FreeItem;
use Lunar\Base\ValueObjects\Cart\Promotion;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Database\Factories\CartFactory;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\FingerprintMismatchException;
use Lunar\Facades\DB;
use Lunar\Facades\ShippingManifest;
use Lunar\Pipelines\Cart\Calculate;
use Lunar\Validation\Cart\ValidateCartForOrderCreation;

/**
 * @property int $id
 * @property ?int $user_id
 * @property ?int $customer_id
 * @property ?int $merged_id
 * @property int $currency_id
 * @property int $channel_id
 * @property ?int $order_id
 * @property ?string $coupon_code
 * @property ?\Illuminate\Support\Carbon $completed_at
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Cart extends BaseModel implements Contracts\Cart
{
    use CachesProperties;
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * Array of cachable class properties.
     *
     * @var array
     */
    public $cachableProperties = [
        'subTotal',
        'shippingTotal',
        'taxTotal',
        'discounts',
        'discountTotal',
        'discountBreakdown',
        'total',
        'taxBreakdown',
        'promotions',
        'freeItems',
    ];

    /**
     * The cart sub total.
     * Sum of cart line amounts, before tax, shipping and cart-level discounts.
     */
    public ?Price $subTotal = null;

    /**
     * The cart sub total.
     * Sum of cart line amounts, before tax, shipping minus discount totals.
     */
    public ?Price $subTotalDiscounted = null;

    /**
     * The shipping sub total for the cart.
     */
    public ?Price $shippingSubTotal = null;

    /**
     * The shipping total for the cart.
     */
    public ?Price $shippingTotal = null;

    /**
     * The cart tax total.
     * Sum of all tax to pay across cart lines and shipping.
     */
    public ?Price $taxTotal = null;

    /**
     * The discount total.
     * Sum of all cart line discounts and cart-level discounts.
     */
    public ?Price $discountTotal = null;

    /**
     * All the discount breakdowns for the cart.
     *
     * @var null|Collection<DiscountBreakdown>
     */
    public ?Collection $discountBreakdown = null;

    /**
     * The shipping override to use for the cart.
     */
    public ?ShippingOption $shippingOptionOverride = null;

    /**
     * Additional shipping estimate meta data.
     */
    public array $shippingEstimateMeta = [];

    /**
     * All the shipping breakdowns for the cart.
     */
    public ?ShippingBreakdown $shippingBreakdown = null;

    /**
     * The cart total.
     * Sum of the cart-line amounts, shipping and tax, minus cart-level discount amount.
     */
    public ?Price $total = null;

    /**
     * All the tax breakdowns for the cart.
     *
     * @var null|Collection<TaxBreakdown>
     */
    public ?TaxBreakdown $taxBreakdown = null;

    /**
     * The cart-level promotions.
     *
     * @var null|Collection<Promotion>
     */
    public ?Collection $promotions = null;

    /**
     * The cart-level discounts.
     *
     * @var null|Collection<Discount>
     */
    public ?Collection $discounts = null;

    /**
     * Qualifying promotional free items.
     *
     * @var null|Collection<FreeItem>
     */
    public ?Collection $freeItems = null;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
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
        'meta' => AsArrayObject::class,
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(CartLine::modelClass(), 'cart_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::modelClass());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::modelClass());
    }

    public function scopeUnmerged(Builder $query): Builder
    {
        return $query->whereNull('merged_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CartAddress::modelClass(), 'cart_id');
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(CartAddress::modelClass(), 'cart_id')->whereType('shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(CartAddress::modelClass(), 'cart_id')->whereType('billing');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::modelClass());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereDoesntHave('orders')->orWhereHas('orders', function ($query) {
            return $query->whereNull('placed_at');
        });
    }

    public function draftOrder(int $draftOrderId = null): HasOne
    {
        return $this->hasOne(Order::modelClass())
            ->when($draftOrderId, function (Builder $query, int $draftOrderId) {
                $query->where('id', $draftOrderId);
            })->whereNull('placed_at');
    }

    public function completedOrder(int $completedOrderId = null): HasOne
    {
        return $this->hasOne(Order::modelClass())
            ->when($completedOrderId, function (Builder $query, int $completedOrderId) {
                $query->where('id', $completedOrderId);
            })->whereNotNull('placed_at');
    }

    public function completedOrders(): HasMany
    {
        return $this->hasMany(Order::modelClass())
            ->whereNotNull('placed_at');
    }

    public function hasCompletedOrders(): bool
    {
        return (bool) $this->completedOrders()->count();
    }

    /**
     * Calculate the cart totals and cache the result.
     */
    public function calculate(bool $force = false): Cart
    {
        if (! $force && $this->isCalculated()) {
            // Don't recalculate
            return $this;
        }

        $cart = app(Pipeline::class)
            ->send($this)
            ->through(
                config('lunar.cart.pipelines.cart', [
                    Calculate::class,
                ])
            )->thenReturn();

        return $cart->cacheProperties();
    }

    /**
     * Force the cart to recalculate.
     */
    public function recalculate(): Cart
    {
        return $this->calculate(force: true);
    }

    public function isCalculated(): bool
    {
        return ! blank($this->total) && $this->lines->every(
            fn (CartLine $line) => ! blank($line->total)
        );
    }

    /**
     * Add or update a purchasable item to the cart
     */
    public function add(Purchasable $purchasable, int $quantity = 1, array $meta = [], bool $refresh = true): Cart
    {
        foreach (config('lunar.cart.validators.add_to_cart', []) as $action) {
            // Throws a validation exception?
            app($action)->using(
                cart: $this,
                purchasable: $purchasable,
                quantity: $quantity,
                meta: $meta
            )->validate();
        }

        return app(
            config('lunar.cart.actions.add_to_cart', AddOrUpdatePurchasable::class)
        )->execute($this, $purchasable, $quantity, $meta)
            ->then(fn () => $refresh ? $this->refresh()->recalculate() : $this);
    }

    public function addLines(iterable $lines): Cart
    {
        DB::transaction(function () use ($lines) {
            collect($lines)->each(function ($line) {
                $this->add(
                    purchasable: $line['purchasable'],
                    quantity: $line['quantity'],
                    meta: (array) ($line['meta'] ?? null),
                    refresh: false
                );
            });
        });

        return $this->refresh()->recalculate();
    }

    public function remove(int $cartLineId, bool $refresh = true): Cart
    {
        foreach (config('lunar.cart.validators.remove_from_cart', []) as $action) {
            app($action)->using(
                cart: $this,
                cartLineId: $cartLineId,
            )->validate();
        }

        return app(
            config('lunar.cart.actions.remove_from_cart', RemovePurchasable::class)
        )->execute($this, $cartLineId)
            ->then(fn () => $refresh ? $this->refresh()->recalculate() : $this);
    }

    public function updateLine(int $cartLineId, int $quantity, array $meta = null, bool $refresh = true): Cart
    {
        foreach (config('lunar.cart.validators.update_cart_line', []) as $action) {
            app($action)->using(
                cart: $this,
                cartLineId: $cartLineId,
                quantity: $quantity,
                meta: $meta
            )->validate();
        }

        return app(
            config('lunar.cart.actions.update_cart_line', UpdateCartLine::class)
        )->execute($cartLineId, $quantity, $meta)
            ->then(fn () => $refresh ? $this->refresh()->recalculate() : $this);
    }

    public function updateLines(Collection $lines): Cart
    {
        DB::transaction(function () use ($lines) {
            $lines->each(function ($line) {
                $this->updateLine(
                    cartLineId: $line['id'],
                    quantity: $line['quantity'],
                    meta: $line['meta'] ?? null,
                    refresh: false
                );
            });
        });

        return $this->refresh()->recalculate();
    }

    public function clear(): Cart
    {
        $this->lines()->delete();

        return $this->refresh()->recalculate();
    }

    public function associate(Authenticatable $user, string $policy = 'merge', bool $refresh = true): Cart
    {
        if ($this->customer()->exists()) {
            if (! $user->query()
                ->whereHas('customers', fn ($query) => $query->where('customer_id', $this->customer->id))
                ->exists()) {
                throw new Exception('Invalid user');
            }
        }

        return app(
            config('lunar.cart.actions.associate_user', AssociateUser::class)
        )->execute($this, $user, $policy)
            ->then(fn () => $refresh ? $this->refresh()->recalculate() : $this);
    }

    public function setCustomer(Customer $customer): Cart
    {
        if ($this->user()->exists()) {
            if (! $customer->query()
                ->whereHas('users', fn ($query) => $query->where('user_id', $this->user->id))
                ->exists()) {
                throw new Exception('Invalid customer');
            }
        }

        $this->customer()->associate($customer)->save();

        return $this->refresh()->recalculate();
    }

    public function addAddress(array|Addressable $address, string $type, bool $refresh = true): Cart
    {
        foreach (config('lunar.cart.validators.add_address', []) as $action) {
            app($action)->using(
                cart: $this,
                address: $address,
                type: $type,
            )->validate();
        }

        return app(
            config('lunar.cart.actions.add_address', AddAddress::class)
        )->execute($this, $address, $type)
            ->then(fn () => $refresh ? $this->refresh()->recalculate() : $this);
    }

    public function setShippingAddress(array|Addressable $address): Cart
    {
        return $this->addAddress($address, 'shipping');
    }

    public function setBillingAddress(array|Addressable $address): Cart
    {
        return $this->addAddress($address, 'billing');
    }

    public function setShippingOption(ShippingOption $option, bool $refresh = true): Cart
    {
        foreach (config('lunar.cart.validators.set_shipping_option', []) as $action) {
            app($action)->using(
                cart: $this,
                shippingOption: $option,
            )->validate();
        }

        return app(
            config('lunar.cart.actions.set_shipping_option', SetShippingOption::class)
        )->execute($this, $option)
            ->then(fn () => $refresh ? $this->refresh()->recalculate() : $this);
    }

    public function getShippingOption(): ?ShippingOption
    {
        return ShippingManifest::getShippingOption($this);
    }

    public function isShippable(): bool
    {
        return (bool) $this->lines->filter(function ($line) {
            return $line->purchasable->isShippable();
        })->count();
    }

    public function createOrder(
        bool $allowMultipleOrders = false,
        int $orderIdToUpdate = null
    ): Order {
        foreach (config('lunar.cart.validators.order_create', [
            ValidateCartForOrderCreation::class,
        ]) as $action) {
            app($action)->using(
                cart: $this,
            )->validate();
        }

        return app(
            config('lunar.cart.actions.order_create', CreateOrder::class)
        )->execute(
            $this->refresh()->recalculate(),
            $allowMultipleOrders,
            $orderIdToUpdate
        )->then(fn ($order) => $order->refresh());
    }

    /**
     * Returns whether a cart has enough info to create an order.
     */
    public function canCreateOrder(): bool
    {
        $passes = true;

        foreach (config('lunar.cart.validators.order_create', [
            ValidateCartForOrderCreation::class,
        ]) as $action) {
            try {
                app($action)->using(
                    cart: $this,
                )->validate();
            } catch (CartException $e) {
                $passes = false;
            }
        }

        return $passes;
    }

    public function fingerprint(): string
    {
        $generator = config('lunar.cart.fingerprint_generator', GenerateFingerprint::class);

        return (new $generator())->execute($this);
    }

    public function checkFingerprint(string $fingerprint): bool
    {
        return tap($fingerprint == $this->fingerprint(), function ($result) {
            throw_unless(
                $result,
                FingerprintMismatchException::class
            );
        });
    }

    public function getEstimatedShipping(array $params, bool $setOverride = false): ?ShippingOption
    {
        $this->shippingEstimateMeta = $params;
        $option = ShippingManifest::getOptions($this)
            ->filter(
                fn ($option) => ! $option->collect
            )->sortBy('price.value')->first();

        if ($setOverride && $option) {
            $this->shippingOptionOverride = $option;
        }

        return $option;
    }
}
