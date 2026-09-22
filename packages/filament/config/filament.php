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
    | Record URL resolvers
    |--------------------------------------------------------------------------
    |
    | Bridge tables and widgets link to per-record management pages owned by
    | whichever panel is consuming them. Each entry is a Filament page class
    | string, called as `{class}::getUrl(['record' => $record])`, or a
    | `[Resource::class, 'page']` pair, called as
    | `{resource}::getUrl('page', ['record' => $record])`. A callable receiving
    | ($record, $context) is also accepted, but closures do not survive
    | `config:cache`. Null disables the link. The `lunarphp/admin` shell
    | overrides these defaults at boot.
    |
    */
    'record_urls' => [
        'order' => null,
        'product_variant' => null,
        'collection_edit' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable Variants
    |--------------------------------------------------------------------------
    |
    | When `true` this will show the Variants manager when editing a product. If your
    | storefront doesn't support variants, set this to false.
    |
    */
    'enable_variants' => true,

    /*
    |--------------------------------------------------------------------------
    | PDF Streaming
    |--------------------------------------------------------------------------
    |
    | When handling PDF's when browsing the resource, you can decide whether to stream the
    | PDF in a new tab or download the PDF to your hard drive.
    |
    | Available options are 'download' or 'stream'
    |
    */
    'pdf_rendering' => 'download',

    /*
    |--------------------------------------------------------------------------
    | Enable Scout when searching on supported models.
    |--------------------------------------------------------------------------
    |
    | Some models in the core have Scout implemented as a search driver, if you
    | want to use Scout when possible when searching, enable it here.
    |
    */
    'scout_enabled' => false,
];
