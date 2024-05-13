<?php

namespace Lunar\Managers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Session\SessionManager;
use Lunar\Base\CartSessionInterface;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;

class CartSessionManager implements CartSessionInterface
{
    public function __construct(
        protected SessionManager $sessionManager,
        protected AuthManager $authManager,
        protected $channel = null,
        protected $currency = null,
        public $cart = null
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function current($estimateShipping = false)
    {
        return $this->fetchOrCreate(
            config('lunar.cart.auto_create', false),
            estimateShipping: $estimateShipping
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
    public function forget()
    {
        $this->sessionManager->forget('shipping_estimate_meta');
        $this->sessionManager->forget(
            $this->getSessionKey()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function manager()
    {
        if (! $this->cart) {
            $this->fetchOrCreate(create: true);
        }

        return $this->cart;
    }

    /**
     * {@inheritDoc}
     */
    public function associate(Cart $cart, Authenticatable $user, $policy)
    {
        $this->use(
            $cart->associate($user, $policy)
        );
    }

    /**
     * Set the cart to be used for the session.
     *
     * @return \Lunar\Models\Cart
     */
    public function use(Cart $cart)
    {
        $this->sessionManager->put(
            $this->getSessionKey(),
            $cart->id
        );

        return $this->cart = $cart;
    }

    /**
     * Fetches a cart and optionally creates one if it doesn't exist.
     *
     * @param  bool  $create
     * @return \Lunar\Models\Cart|null
     */
    private function fetchOrCreate($create = false, bool $estimateShipping = false)
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

        $this->cart = Cart::with(
            config('lunar.cart.eager_load', [])
        )->find($cartId);

        if (! $this->cart) {
            if (! $create) {
                return null;
            }

            return $this->createNewCart();
        }

        if ($estimateShipping) {
            // Some shipping drivers might require sub totals to be present
            // before they can estimate a shipping cost, doing this in the driver
            // itself can lead to infinite loops, so we calculate before.
            $this->cart->calculate();
            $this->cart->getEstimatedShipping(
                $this->getShippingEstimateMeta(),
                setOverride: true
            );
        }

        return $this->cart->calculate();
    }

    /**
     * Get the cart session key.
     */
    public function getSessionKey()
    {
        return config('lunar.cart.session_key');
    }

    /**
     * Set the current channel.
     *
     * @return void
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;

        if ($this->current() && $this->current()->channel_id != $channel->id) {
            $this->cart->update([
                'channel_id' => $channel->id,
            ]);
        }
    }

    /**
     * Set the current currency.
     *
     * @return void
     */
    public function setCurrency(Currency $currency)
    {
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
        return $this->currency ?: Currency::getDefault();
    }

    /**
     * Return the current channel.
     */
    public function getChannel(): Channel
    {
        return $this->channel ?: Channel::getDefault();
    }

    /**
     * Return available shipping options for the current cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getShippingOptions()
    {
        return ShippingManifest::getOptions(
            $this->current()
        );
    }

    /**
     * Create an order from a cart instance.
     *
     * @param  bool  $forget
     * @return \Lunar\Models\Order
     */
    public function createOrder($forget = true)
    {
        if ($forget) {
            $this->forget();
        }

        return $this->manager()->createOrder();
    }

    /**
     * Create a new cart instance.
     *
     * @return \Lunar\Models\Cart
     */
    protected function createNewCart()
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
        if (! $this->cart) {
            $this->cart = $this->fetchOrCreate(true);
        }

        return $this->cart->{$method}(...$args);
    }
}
