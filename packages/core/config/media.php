<?php

use Lunar\Base\StandardMediaDefinitions;

return [

    /*
    |--------------------------------------------------------------------------
    | Media Definition
    |--------------------------------------------------------------------------
    |
    | Specify which media definition should be used when generating media.
    |
    | note: if extended Lunar's model, you should update the key,
    |       or default definition will be used
    |
    | example: App\Models\Product::class => CustomMediaDefinitions::class,
    |
    */
    'definitions' => [
        Lunar\Models\Asset::class => StandardMediaDefinitions::class,
        Lunar\Models\Brand::class => StandardMediaDefinitions::class,
        Lunar\Models\Collection::class => StandardMediaDefinitions::class,
        Lunar\Models\Product::class => StandardMediaDefinitions::class,
        Lunar\Models\ProductOption::class => StandardMediaDefinitions::class,
        Lunar\Models\ProductOptionValue::class => StandardMediaDefinitions::class,
    ],

    'collection' => 'images',

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
