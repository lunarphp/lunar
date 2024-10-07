<?php

namespace Lunar\Managers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Lunar\Base\CartSessionInterface;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Contracts\Channel as ChannelContract;
use Lunar\Models\Contracts\Currency as CurrencyContract;
use Lunar\Models\Currency;
use Lunar\Models\Order;

class CartSessionManager implements CartSessionInterface
{
    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager,
        protected ChannelContract $channel,
        protected CurrencyContract $currency,
        public CartContract $cart,
    ) {
        //
    }

    public function allowsMultipleOrdersPerCart(): bool
    {
        return config('lunar.cart_session.allow_multiple_per_order', false);
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
    public function forget(bool $delete = true): void
    {
        if ($delete) {
            Cart::destroy(
                $this->sessionManager->get(
                    $this->getSessionKey()
                )
            );
        }

        unset($this->cart);

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
    public function associate(CartContract $cart, Authenticatable $user, $policy): void
    {
        /** @var Cart $cart */
        $this->use(
            $cart->associate($user, $policy)
        );
    }

    /**
     * Set the cart to be used for the session.
     */
    public function use(CartContract $cart): CartContract
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
    private function fetchOrCreate(bool $create = false, bool $estimateShipping = false, bool $calculate = true): ?Cart
    {
        $cartId = $this->sessionManager->get(
            $this->getSessionKey()
        );

        if (! $cartId && $user = $this->authManager->user()) {
            $cartId = $user->carts()->active()->first()?->id;
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
    public function setChannel(ChannelContract $channel): void
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
    public function setCurrency(CurrencyContract $currency): void
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
    public function getCurrency(): CurrencyContract
    {
        return $this->currency?->exists ? $this->currency : Currency::modelClass()::getDefault();
    }

    /**
     * Return the current channel.
     */
    public function getChannel(): ChannelContract
    {
        return $this->channel?->exists ? $this->channel : Channel::modelClass()::getDefault();
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
    protected function createNewCart(): CartContract
    {
        $user = $this->authManager->user();

        $cart = Cart::create([
            'currency_id' => $this->getCurrency()->id,
            'channel_id' => $this->getChannel()->id,
            'user_id' => optional($user)->id,
            'customer_id' => optional($user)->latestCustomer()?->id,
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
