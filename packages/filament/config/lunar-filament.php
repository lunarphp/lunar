<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Publishable schema path
    |--------------------------------------------------------------------------
    |
    | When a developer publishes the bridge's schemas, tables, or relation
    | managers via `php artisan vendor:publish --tag=lunar-filament.schemas`,
    | files are copied into this path inside the consuming application.
    |
    */
    'publish_path' => app_path('Filament'),

    /*
    |--------------------------------------------------------------------------
    | Resolver strategy
    |--------------------------------------------------------------------------
    |
    | When `prefer_published` is true the runtime resolver returns a
    | published copy of a schema/table class if it exists in the consumer
    | app namespace, falling back to the bridge class otherwise. Set this
    | to false to always use the bridge classes regardless of publication.
    |
    */
    'resolver' => [
        'prefer_published' => true,
        'app_namespace' => 'App\\Filament',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget auto-registration
    |--------------------------------------------------------------------------
    |
    | The Lunar admin shell wires every bridge widget into its panel. When
    | you build your own Filament panel using lunarphp/filament directly,
    | leave this disabled and register only the widgets you want via your
    | own `widgets([…])` configuration.
    |
    */
    'register_widgets_on_default_panel' => false,

    /*
    |--------------------------------------------------------------------------
    | Record URL resolvers
    |--------------------------------------------------------------------------
    |
    | Bridge tables and widgets link to per-record management pages owned by
    | whichever panel is consuming them. Each entry is a Filament resource or
    | page class string. The bridge calls `{class}::getUrl(['record' => $record])`
    | when present; an empty string disables the link. The `lunarphp/admin`
    | shell overrides these defaults at boot.
    |
    */
    'record_urls' => [
        'order' => null,
        'product_variant' => null,
    ],
];
