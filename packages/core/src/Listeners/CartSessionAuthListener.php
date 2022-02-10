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
     * @param  \App\Events\OrderShipped  $event
     * @return void
     */
    public function login(Login $event)
    {
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
            $userCart = Cart::whereUserId($event->user->getKey())->latest()->first();

            if ($userCart) {
                CartSession::use($userCart);
            }
        }
    }

    /**
     * Handle the logout event.
     *
     * @param  \App\Events\OrderShipped  $event
     * @return void
     */
    public function logout(Logout $event)
    {
        CartSession::forget();
    }
}
