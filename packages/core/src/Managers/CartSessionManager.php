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
    public function current()
    {
        return $this->fetchOrCreate(
            config('lunar.cart.auto_create', false)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function forget()
    {
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
            $this->fetchOrCreate(create:true);
        }

        return $this->cart->getManager();
    }

    /**
     * {@inheritDoc}
     */
    public function associate(Cart $cart, Authenticatable $user, $policy)
    {
        $this->use(
            $cart->getManager()->associate($user, $policy)
        );
    }

    /**
     * {@inheritDoc}
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
    private function fetchOrCreate($create = false)
    {
        $cartId = $this->sessionManager->get(
            $this->getSessionKey()
        );

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

        return $this->cart->getManager()->getCart();
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionKey()
    {
        return config('lunar.cart.session_key');
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     *
     * @return \Lunar\Models\Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency ?: Currency::getDefault();
    }

    /**
     * Return the current channel.
     *
     * @return \Lunar\Models\Channel
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
     * @return void
     */
    protected function createNewCart()
    {
        $cart = Cart::create([
            'currency_id' => $this->getCurrency()->id,
            'channel_id' => $this->getChannel()->id,
            'user_id' => $this->authManager->user()?->id,
        ]);

        return $this->use($cart);
    }

    public function __call($method, $args)
    {
        return $this->manager()->{$method}(...$args);
    }
}
