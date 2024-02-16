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
        Lunar\Models\Asset::class => 'asset',
        Lunar\Models\Brand::class => 'brand',
        Lunar\Models\Collection::class => 'collection',
        Lunar\Models\Product::class => 'product',
        Lunar\Models\ProductOption::class => 'product_option',
        Lunar\Models\ProductOptionValue::class => 'product_option_value',
        'default' => 'images',
    ],

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
