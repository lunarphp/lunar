<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront surface
    |--------------------------------------------------------------------------
    |
    | Guests and customers. `guard` names the host auth guard that protects
    | the customer area (`/me`); null leaves those endpoints unregistered.
    | Set `register_routes` to false to require the route files yourself.
    |
    */
    'storefront' => [
        'enabled' => true,
        'prefix' => 'api/storefront',
        'guard' => null,
        'register_routes' => true,
        'middleware' => ['lunar.api.storefront'],
        'throttle' => 60,
        'cart_token_ttl_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin surface
    |--------------------------------------------------------------------------
    |
    | Staff and machine integrations. The package registers the `lunar-api`
    | guard backed by Lunar's own API keys; point `guard` at another guard
    | (Passport, an SSO proxy) as long as its user answers `can()`.
    |
    */
    'admin' => [
        'enabled' => true,
        'prefix' => 'api/admin',
        'guard' => 'lunar-api',
        'register_guard' => true,
        'register_routes' => true,
        'middleware' => ['lunar.api.admin'],
        'throttle' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Query grammar limits
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_size' => 15,
        'max_size' => 100,
        'max_include_depth' => 3,
    ],

];
