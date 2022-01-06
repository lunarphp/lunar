<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use Illuminate\Contracts\Auth\Authenticatable;

interface CartSessionInterface
{
    /**
     * Return the current cart.
     *
     * @return \GetCandy\Models\Cart|null
     */
    public function current();

    /**
     * Forget the current cart session.
     *
     * @return void
     */
    public function forget();

    /**
     * Return the cart manager instance.
     *
     * @return \GetCandy\Managers\CartManager
     */
    public function manager();

    /**
     * Associate a cart to a user.
     *
     * @param \GetCandy\Models\Cart                      $cart
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $policy
     *
     * @return void
     */
    public function associate(Cart $cart, Authenticatable $user, $policy);

    /**
     * Use the given cart and set to the session.
     *
     * @param \GetCandy\Models\Cart $cart
     *
     * @return void
     */
    public function use(Cart $cart);

    /**
     * Return the session key for carts.
     *
     * @return string
     */
    public function getSessionKey();

    /**
     * Set the cart session channel.
     *
     * @param \GetCandy\Models\Channel $channel
     *
     * @return self
     */
    public function setChannel(Channel $channel);

    /**
     * Set the cart session currency.
     *
     * @param \GetCandy\Models\Currency $currency
     *
     * @return self
     */
    public function setCurrency(Currency $currency);

    /**
     * Return the current currency.
     *
     * @return \GetCandy\Models\Currency
     */
    public function getCurrency(): Currency;

    /**
     * Return the current channel.
     *
     * @return \GetCandy\Models\Channel
     */
    public function getChannel(): Channel;
}
