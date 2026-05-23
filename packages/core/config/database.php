<?php

return [

    'connection' => null,

    'table_prefix' => 'lunar_',

    /*
    |--------------------------------------------------------------------------
    | Morph Prefix
    |--------------------------------------------------------------------------
    |
    | If you wish to prefix Lunar's morph mapping in the database, you can
    | set that here e.g. `lunar_product` instead of `product`
    |
    */
    'morph_prefix' => null,

    /*
    |--------------------------------------------------------------------------
    | Users Table ID
    |--------------------------------------------------------------------------
    |
    | Lunar adds a relationship to your 'users' table and by default assumes
    | a 'bigint'. You can change this to either an 'int' or 'uuid'.
    |
    */
    'users_id_type' => 'bigint',

    /*
    |--------------------------------------------------------------------------
    | Disable migrations
    |--------------------------------------------------------------------------
    |
    | Prevent Lunar`s default package migrations from running for the core.
    | Set to 'true' to disable.
    |
    */
    'disable_migrations' => false,

    /*
    |--------------------------------------------------------------------------
    | Prevent Lazy Loading
    |--------------------------------------------------------------------------
    |
    | When enabled, accessing an Eloquent relation on a Lunar model that has
    | not been eager-loaded throws a LunarLazyLoadingViolation. The same
    | switch also enforces accessing missing attributes and silently
    | discarded mass-assigned attributes on Lunar models. Defaults to
    | 'auto' — enforced outside production. Set to true to enforce
    | everywhere (recommended once your code is clean) or false to disable.
    |
    */
    'prevent_lazy_loading' => env('LUNAR_PREVENT_LAZY_LOADING', 'auto'),

];
