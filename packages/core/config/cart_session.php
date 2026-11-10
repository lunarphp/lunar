<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | Specify the session key used when fetching the cart.
    |
    */
    'session_key' => 'lunar_cart',

    /*
    |--------------------------------------------------------------------------
    | Auto create a cart when none exists for user.
    |--------------------------------------------------------------------------
    |
    | Determines whether you want to automatically create a cart for a user if
    | they do not currently have one in the session. By default, this is false
    | to minimise the amount of carts added to the database.
    |
    */
    'auto_create' => false,

    /*
    |--------------------------------------------------------------------------
    | Allow Carts to have multiple orders associated.
    |--------------------------------------------------------------------------
    |
    | Determines whether the same cart instance will be returned if there is already
    | a completed order associated to the cart which is retrieved in the session.
    | When set to false, if a cart has a completed order, then a new instance
    | of a cart will be returned, even if auto_create is set to false
    |
    */
    'allow_multiple_orders_per_cart' => false,

    /*
    |--------------------------------------------------------------------------
    | Delete cart on logout
    |--------------------------------------------------------------------------
    |
    | Determines whether the cart should be soft deleted when the user logs out.
    |
    */
    'delete_on_forget' => true,

    /*
    |--------------------------------------------------------------------------
    | Remember guest carts
    |--------------------------------------------------------------------------
    |
    | When enabled, a guest cart is also pointed to by a long-lived cookie, so
    | it can be restored after the session expires. This lets you keep a short
    | session lifetime without losing guest carts. The cookie's expiry is
    | refreshed each time it restores a cart, and the cart is touched, so a
    | returning guest keeps their cart for as long as they keep visiting.
    |
    | Keep the lifetime (in minutes) shorter than `lunar.cart.prune_tables
    | .prune_interval`, and leave `auto_create` off so crawlers that never add
    | to cart are not given a cart or a cookie.
    |
    */
    'remember' => [
        'enabled' => true,
        'cookie' => 'lunar_cart_remember',
        'lifetime' => 60 * 24 * 30,
    ],
];
