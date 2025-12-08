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
    | Determines whether the cart sholud be soft deleted when the user logs out.
    |
    */
    'delete_on_forget' => true,
];
