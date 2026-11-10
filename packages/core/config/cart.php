<?php

return [
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
    | Persisted totals
    |--------------------------------------------------------------------------
    |
    | Each calculation writes its totals to the cart and line rows, and the
    | next calculate() is served from those rows while nothing on the cart
    | has changed and the snapshot is younger than the TTL (in seconds).
    | The TTL bounds staleness from inputs the cart cannot see: price edits,
    | discount end dates, tax and shipping rule changes. 0 never serves the
    | snapshot; it is still written so the reporting columns stay populated.
    | Checkout and payment always recalculate (see Cart::recalculate()).
    |
    */
    'totals' => [
        'ttl' => 300,
    ],

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
        'lines.purchasable.taxClass',
        'lines.purchasable.values',
        'lines.purchasable.product.thumbnail',
        'lines.purchasable.prices.currency',
        'lines.purchasable.prices.priceable',
        'lines.purchasable.product',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prune carts
    |--------------------------------------------------------------------------
    |
    | Should the cart models be pruned to prevent data build up and
    | some settings controlling how pruning should be determined
    |
    */
    'prune_tables' => [

        'enabled' => false,

        'prune_interval' => 90, // days

    ],
];
