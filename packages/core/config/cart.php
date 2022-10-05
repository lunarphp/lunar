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
    | they do not currently have one in the session. By default this is false
    | to minimise the amount of cart lines added to the database.
    |
    */
    'auto_create' => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication policy
    |--------------------------------------------------------------------------
    |
    | When a user logs in, by default, Lunar will merge the current (guest) cart
    | with the users current cart, if they have one.
    | Available options: 'merge', 'override'
    |
    */
    'auth_policy' => 'merge',

    /*
    |--------------------------------------------------------------------------
    | Default eager loading
    |--------------------------------------------------------------------------
    |
    | When loading up a cart and doing calculations, there's a few relationships
    | that are used when it's running. Here you can define which relationships
    | should be eager loaded when these calculations take place.
    |
    */
    'eager_load' => [
        'currency',
        'shippingAddress',
        'billingAddress',
        'lines.purchasable.prices.currency',
        'lines.purchasable.prices.priceable',
        'lines.purchasable.product',
        'lines.cart',
    ],
];
