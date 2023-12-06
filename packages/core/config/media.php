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

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
