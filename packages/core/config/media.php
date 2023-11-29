<?php

use Lunar\Base\StandardMediaCollections;

return [

    'collections' => [
        Lunar\Models\Asset::class => StandardMediaCollections::class,
        Lunar\Models\Brand::class => StandardMediaCollections::class,
        Lunar\Models\Collection::class => StandardMediaCollections::class,
        Lunar\Models\Product::class => StandardMediaCollections::class,
        Lunar\Models\ProductOption::class => StandardMediaCollections::class,
        Lunar\Models\ProductOptionValue::class => StandardMediaCollections::class,
    ],

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
