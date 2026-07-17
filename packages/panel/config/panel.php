<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel Path
    |--------------------------------------------------------------------------
    |
    | The URI prefix the panel is served under.
    |
    */
    'path' => 'panel',

    /*
    |--------------------------------------------------------------------------
    | Panel Name
    |--------------------------------------------------------------------------
    */
    'name' => 'Lunar',

    /*
    |--------------------------------------------------------------------------
    | Auth Guard
    |--------------------------------------------------------------------------
    |
    | When null, the panel authenticates against the staff guard configured
    | in lunar.staff.guard.
    |
    */
    'guard' => null,

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    */
    'route_middleware' => ['web'],

    'storefront_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Edit Drafts
    |--------------------------------------------------------------------------
    |
    | Drafts untouched for ttl_days are pruned by the scheduled model:prune
    | run; their base snapshots are too stale for reliable conflict checks.
    |
    */
    'drafts' => [
        'ttl_days' => 7,
    ],

    'support_url' => 'https://docs.lunarphp.com/',

    /*
    |--------------------------------------------------------------------------
    | Menus
    |--------------------------------------------------------------------------
    |
    | Optional top-level menu grouping. Each entry maps section keys into a
    | named menu: ['key' => 'catalog', 'label' => 'Catalog',
    | 'icon' => 'package', 'sections' => ['catalog']].
    |
    */
    'menus' => [],

];
