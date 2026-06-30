<?php

namespace Lunar\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Lunar\Core\Casts\CouponString;
use Lunar\Core\Contracts\Actions\Carts\AddsAddress;
use Lunar\Core\Contracts\Actions\Carts\AddsOrUpdatesPurchasable;
use Lunar\Core\Contracts\Actions\Carts\AssociatesUser;
use Lunar\Core\Contracts\Actions\Carts\CreatesOrder;
use Lunar\Core\Contracts\Actions\Carts\GeneratesFingerprint;
use Lunar\Core\Contracts\Actions\Carts\RemovesPurchasable;
use Lunar\Core\Contracts\Actions\Carts\SetsShippingOption;
use Lunar\Core\Contracts\Actions\Carts\UpdatesCartLine;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Contracts\LunarUser;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Database\Factories\CartFactory;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataObjects\StorefrontContext;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Exceptions\FingerprintMismatchException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Concerns\CachesProperties;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Pipelines\Cart\Calculate;
use Lunar\Core\Validation\Cart\ValidateCartForOrderCreation;
use Lunar\Core\Validation\CartLine\CartLineStock;
use Lunar\Core\ValueObjects\Cart\DiscountBreakdown;
use Lunar\Core\ValueObjects\Cart\FreeItem;
use Lunar\Core\ValueObjects\Cart\Promotion;
use Lunar\Core\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\ValueObjects\Cart\TaxBreakdown;

/**
 * @property int $id
 * @property ?int $user_id
 * @property ?int $customer_id
 * @property ?int $merged_id
 * @property int $currency_id
 * @property int $channel_id
 * @property ?int $region_id
 * @property ?int $tax_zone_id
 * @property ?int $order_id
 * @property ?string $coupon_code
 * @property ?Carbon $completed_at
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $deleted_at
 */
class Cart extends Base
{
    use CachesProperties;
    use HasFactory;
    use HasMacros;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Array of cachable class properties.
     *
     * @var array
     */
    public $cachableProperties = [
        'subTotal',
        'shippingSubTotal',
        'shippingTaxTotal',
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
    public ?PriceValue $subTotal = null;

    /**
     * The cart sub total.
     * Sum of cart line amounts, before tax, shipping minus discount totals.
     */
    public ?PriceValue $subTotalDiscounted = null;

    /**
     * The shipping sub total for the cart.
     */
    public ?PriceValue $shippingSubTotal = null;

    /**
     * The shipping tax total for the cart.
     */
    public ?PriceValue $shippingTaxTotal = null;

    /**
     * The shipping total for the cart.
     */
    public ?PriceValue $shippingTotal = null;

    /**
     * The cart tax total.
     * Sum of all tax to pay across cart lines and shipping.
     */
    public ?PriceValue $taxTotal = null;

    /**
     * The discount total.
     * Sum of all cart line discounts and cart-level discounts.
     */
    public ?PriceValue $discountTotal = null;

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
    public ?PriceValue $total = null;

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
        'coupon_code' => CouponString::class,
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(CartLine::class, 'cart_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Produce a storefront context from the cart's stored selections, so cart
     * logic and pre-cart browse logic consume the same explicit type.
     */
    public function context(): StorefrontContext
    {
        return app(ResolvesStorefrontContext::class)->execute(
            channel: $this->channel,
            currency: $this->currency,
            customer: $this->customer,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function taxZone(): BelongsTo
    {
        return $this->belongsTo(TaxZone::class);
    }

    public function scopeUnmerged(Builder $query): Builder
    {
        return $query->whereNull('merged_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CartAddress::class, 'cart_id');
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(CartAddress::class, 'cart_id')->whereType('shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(CartAddress::class, 'cart_id')->whereType('billing');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q
                ->whereDoesntHave('orders')
                ->orWhereHas('orders', function ($sub) {
                    $sub->whereNull('placed_at');
                });
        });
    }

    /**
     * Return the draft order relationship.
     */
    public function draftOrder(?int $draftOrderId = null): HasOne
    {
        return $this->hasOne(Order::class)
            ->when($draftOrderId, function (Builder $query, int $draftOrderId) {
                $query->where('id', $draftOrderId);
            })->whereNull('placed_at');
    }

    public function currentDraftOrder(?int $draftOrderId = null)
    {
        return $this->calculate()
            ->draftOrder($draftOrderId)
            ->where('fingerprint', $this->fingerprint())
            ->when(
                $this->total,
                fn (Builder $query, PriceValue $price) => $query->where('total', $price->value)
            )->first();
    }

    /**
     * Return the completed order relationship.
     */
    public function completedOrder(?int $completedOrderId = null): HasOne
    {
        return $this->hasOne(Order::class)
            ->when($completedOrderId, function (Builder $query, int $completedOrderId) {
                $query->where('id', $completedOrderId);
            })->whereNotNull('placed_at');
    }

    public function completedOrders(): HasMany
    {
        return $this->hasMany(Order::class)
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

        app(AddsOrUpdatesPurchasable::class)->execute($this, $purchasable, $quantity, $meta);

        return $refresh ? $this->refresh()->recalculate() : $this;
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

        app(RemovesPurchasable::class)->execute($this, $cartLineId);

        return $refresh ? $this->refresh()->recalculate() : $this;
    }

    /**
     * Update cart line
     */
    public function updateLine(int $cartLineId, int $quantity, ?array $meta = null, bool $refresh = true): Cart
    {
        foreach (config('lunar.cart.validators.update_cart_line', []) as $action) {
            app($action)->using(
                cart: $this,
                cartLineId: $cartLineId,
                quantity: $quantity,
                meta: $meta
            )->validate();
        }

        app(UpdatesCartLine::class)->execute($cartLineId, $quantity, $meta);

        return $refresh ? $this->refresh()->recalculate() : $this;
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

    /**
     * Associate a user to the cart
     *
     * @throws Exception
     */
    public function associate(LunarUser $user, string $policy = 'merge', bool $refresh = true): Cart
    {
        if ($this->customer()->exists()) {
            if (! $user->query()
                ->whereHas('customers', fn ($query) => $query->where('customer_id', $this->customer->id))
                ->exists()) {
                throw new Exception('Invalid user');
            }
        }

        app(AssociatesUser::class)->execute($this, $user, $policy);

        return $refresh ? $this->refresh()->recalculate() : $this;
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

        app(AddsAddress::class)->execute($this, $address, $type);

        return $refresh ? $this->refresh()->recalculate() : $this;
    }

    public function setShippingAddress(array|Addressable $address, bool $clearTaxZone = true): Cart
    {
        if ($clearTaxZone && $this->tax_zone_id) {
            $this->taxZone()->dissociate()->save();
        }

        return $this->addAddress($address, 'shipping');
    }

    public function setBillingAddress(array|Addressable $address): Cart
    {
        return $this->addAddress($address, 'billing');
    }

    public function setShippingOption(ShippingOption $option, bool $refresh = true): Cart
    {
        $this->loadMissing('shippingAddress');

        foreach (config('lunar.cart.validators.set_shipping_option', []) as $action) {
            app($action)->using(
                cart: $this,
                shippingOption: $option,
            )->validate();
        }

        app(SetsShippingOption::class)->execute($this, $option);

        return $refresh ? $this->refresh()->recalculate() : $this;
    }

    public function getShippingOption(): ?ShippingOption
    {
        return ShippingManifest::getShippingOption($this->calculate());
    }

    public function isShippable(): bool
    {
        return (bool) $this->lines->filter(function ($line) {
            return $line->purchasable->isShippable();
        })->count();
    }

    public function createOrder(
        bool $allowMultipleOrders = false,
        ?int $orderIdToUpdate = null
    ): Order {
        $cart = $this->refresh()->recalculate();

        $cart->loadMissing('completedOrder');

        foreach (config('lunar.cart.validators.order_create', [
            ValidateCartForOrderCreation::class,
        ]) as $action) {
            app($action)->using(
                cart: $cart,
            )->validate();
        }

        $order = app(CreatesOrder::class)->execute($cart, $allowMultipleOrders, $orderIdToUpdate);

        return $order->refresh();
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

    /**
     * @throws ValidationException
     */
    public function validateStock(): void
    {
        $this->lines->each(
            fn ($line) => app(CartLineStock::class)->using(
                purchasable: $line->purchasable,
                quantity: $line->quantity,
            )->validate()
        );
    }

    /**
     * Get a unique fingerprint for the cart to identify if the contents have changed.
     */
    public function fingerprint(): string
    {
        return app(GeneratesFingerprint::class)->execute($this);
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

    /**
     * Set the tax zone override for this cart.
     *
     * When set, all tax calculations use this zone instead of resolving one from the shipping address.
     * Pass null to clear the override and fall back to the address-derived (or default) zone.
     * Pass `$refresh = false` to skip persistence and recalculation (useful for previewing without writing).
     */
    public function setTaxZone(?TaxZone $taxZone, bool $refresh = true): Cart
    {
        if ($taxZone) {
            $this->taxZone()->associate($taxZone);
        } else {
            $this->taxZone()->dissociate();
        }

        if (! $refresh) {
            return $this;
        }

        $this->save();

        return $this->refresh()->recalculate();
    }
}
