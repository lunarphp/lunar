<?php

namespace Lunar\Core\Managers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Actions\Storefront\ResolvesStorefrontContext;
use Lunar\Core\Contracts\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;

class CartSessionManager implements CartSession
{
    protected ?Channel $channel = null;

    protected ?Currency $currency = null;

    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager,
        protected ResolvesStorefrontContext $resolveStorefrontContext,
        public ?Cart $cart = null,
    ) {
        //
    }

    public function allowsMultipleOrdersPerCart(): bool
    {
        return config('lunar.cart_session.allow_multiple_orders_per_cart', false);
    }

    /**
     * {@inheritDoc}
     */
    public function current(bool $estimateShipping = false, bool $calculate = true): ?Cart
    {
        return $this->fetchOrCreate(
            config('lunar.cart_session.auto_create', false),
            estimateShipping: $estimateShipping,
            calculate: $calculate,
        );
    }

    /**
     * Set the criteria to use when estimating shipping costs.
     *
     * @return $this
     */
    public function estimateShippingUsing(array $meta): self
    {
        $this->sessionManager->put('shipping_estimate_meta', $meta);

        return $this;
    }

    /**
     * Return the shipping estimate meta.
     */
    public function getShippingEstimateMeta(): array
    {
        return $this->sessionManager->get('shipping_estimate_meta', []);
    }

    /**
     * {@inheritDoc}
     */
    public function forget(?bool $delete = null): void
    {
        $delete = is_null($delete) ? config('lunar.cart_session.delete_on_forget', true) : $delete;

        if ($delete) {
            Cart::destroy(
                $this->sessionManager->get(
                    $this->getSessionKey()
                )
            );
        }

        $this->cart = null;

        $this->sessionManager->forget('shipping_estimate_meta');
        $this->sessionManager->forget(
            $this->getSessionKey()
        );

    }

    /**
     * {@inheritDoc}
     */
    public function manager(): ?Cart
    {
        if (! $this->cart?->exists) {
            $this->fetchOrCreate(create: true);
        }

        return $this->cart;
    }

    /**
     * {@inheritDoc}
     */
    public function associate(Cart $cart, Authenticatable $user, $policy): void
    {
        /** @var Cart $cart */
        $this->use(
            $cart->associate($user, $policy)
        );
    }

    /**
     * Set the cart to be used for the session.
     */
    public function use(Cart $cart): Cart
    {
        /** @var Cart $cart */
        $this->sessionManager->put(
            $this->getSessionKey(),
            $cart->id
        );

        return $this->cart = $cart;
    }

    /**
     * Fetches a cart and optionally creates one if it doesn't exist.
     */
    protected function fetchOrCreate(bool $create = false, bool $estimateShipping = false, bool $calculate = true): ?Cart
    {
        $cartId = $this->sessionManager->get(
            $this->getSessionKey()
        );

        if (! $cartId && $user = $this->authManager->user()) {
            $cartId = $user->carts()->unmerged()->active()->latest('id')->value('id');
        }

        if (! $cartId) {
            return $create ? $this->cart = $this->createNewCart() : null;
        }

        $cart = $this->cart?->exists ? $this->cart : Cart::with(
            config('lunar.cart.eager_load', [])
        )->find($cartId);

        if (! $cart) {
            return $create ? $this->createNewCart() : null;
        }

        if ($cart->hasCompletedOrders() && ! $this->allowsMultipleOrdersPerCart()) {
            return $this->createNewCart();
        }

        $this->cart = $cart;
        if ($calculate) {
            $this->cart->calculate();
        }

        if ($estimateShipping) {
            $this->estimateShipping();
        }

        return $this->use($this->cart);
    }

    public function estimateShipping(): void
    {
        if (! $this->cart?->exists) {
            return;
        }

        // Some shipping drivers might require sub-totals to be present
        // before they can estimate a shipping cost, doing this in the driver
        // itself can lead to infinite loops, so we calculate before.
        $this->cart->calculate();
        $this->cart->getEstimatedShipping(
            $this->getShippingEstimateMeta(),
            setOverride: true
        );
        $this->cart->calculate(force: true);
    }

    /**
     * Get the cart session key.
     */
    public function getSessionKey(): string
    {
        return config('lunar.cart_session.session_key');
    }

    /**
     * Set the current channel.
     */
    public function setChannel(Channel $channel): void
    {
        /** @var Channel $channel */
        $this->channel = $channel;

        if ($this->current() && $this->current()->channel_id != $channel->id) {
            $this->cart->update([
                'channel_id' => $channel->id,
            ]);
        }
    }

    /**
     * Set the current currency.
     */
    public function setCurrency(Currency $currency): void
    {
        /** @var Currency $currency */
        $this->currency = $currency;

        if ($this->current() && $this->current()->currency_id != $currency->id) {
            $this->cart->update([
                'currency_id' => $currency->id,
            ]);
        }
    }

    /**
     * Return the current currency.
     */
    public function getCurrency(): Currency
    {
        return $this->currency?->exists ? $this->currency : Currency::getDefault();
    }

    /**
     * Return the current channel.
     */
    public function getChannel(): Channel
    {
        return $this->channel?->exists ? $this->channel : Channel::getDefault();
    }

    /**
     * Return available shipping options for the current cart.
     */
    public function getShippingOptions(): Collection
    {
        return ShippingManifest::getOptions(
            $this->current()
        );
    }

    /**
     * Create an order from a cart instance.
     */
    public function createOrder(bool $forget = true): Order
    {
        $order = $this->manager()->createOrder(
            allowMultipleOrders: $this->allowsMultipleOrdersPerCart()
        );

        if ($forget) {
            $this->forget();
        }

        return $order;
    }

    /**
     * Create a new cart instance.
     */
    protected function createNewCart(): Cart
    {
        $user = $this->authManager->user();
        $customer = optional($user)->latestCustomer();

        // An explicit setChannel()/setCurrency() override wins; otherwise the
        // resolver supplies channel/currency/region from the default region.
        $context = $this->resolveStorefrontContext->execute(
            channel: $this->channel?->exists ? $this->channel : null,
            currency: $this->currency?->exists ? $this->currency : null,
            customer: $customer,
        );

        $cart = Cart::create([
            'currency_id' => $context->currency->id,
            'channel_id' => $context->channel->id,
            'region_id' => $context->region?->id,
            'user_id' => optional($user)->id,
            'customer_id' => optional($customer)->id,
        ]);

        return $this->use($cart);
    }

    public function __call($method, $args)
    {
        if (! $this->cart?->exists) {
            $this->fetchOrCreate(create: true, calculate: false);
        }

        return $this->cart->{$method}(...$args);
    }
}
