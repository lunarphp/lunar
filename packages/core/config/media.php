<?php

use Lunar\Base\StandardMediaDefinitions;

return [

    'definitions' => [
        Lunar\Models\Asset::class => StandardMediaDefinitions::class,
        Lunar\Models\Brand::class => StandardMediaDefinitions::class,
        Lunar\Models\Collection::class => StandardMediaDefinitions::class,
        Lunar\Models\Product::class => StandardMediaDefinitions::class,
        Lunar\Models\ProductOption::class => StandardMediaDefinitions::class,
        Lunar\Models\ProductOptionValue::class => StandardMediaDefinitions::class,
    ],

    'media_collection' => [
        Lunar\Models\Asset::class => 'assets',
        Lunar\Models\Brand::class => 'brands',
        Lunar\Models\Collection::class => 'collections',
        Lunar\Models\Product::class => 'products',
        Lunar\Models\ProductOption::class => 'product_options',
        Lunar\Models\ProductOptionValue::class => 'product_options_values',
        'default' => 'images',
    ],

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
