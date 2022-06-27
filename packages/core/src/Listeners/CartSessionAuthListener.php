<?php

namespace GetCandy\Listeners;

use GetCandy\Facades\CartSession;
use GetCandy\Models\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class CartSessionAuthListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the login event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function login(Login $event)
    {
        if (! is_getcandy_user($event->user)) {
            return;
        }

        $currentCart = CartSession::current();

        if ($currentCart && ! $currentCart->user_id) {
            CartSession::associate(
                $currentCart,
                $event->user,
                config('getcandy.cart.auth_policy')
            );
        }

        if (! $currentCart) {
            // Does this user have a cart?
            $userCart = Cart::whereUserId($event->user->getKey())->active()->first();

            if ($userCart) {
                CartSession::use($userCart);
            }
        }
    }

    /**
     * Handle the logout event.
     *
     * @param  \Illuminate\Auth\Events\Logout  $event
     * @return void
     */
    public function logout(Logout $event)
    {
        if (! is_getcandy_user($event->user)) {
            return;
        }

        CartSession::forget();
    }
}
