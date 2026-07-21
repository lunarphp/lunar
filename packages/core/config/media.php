<?php

use Lunar\Core\Media\StandardDefinitions;

return [

    'definitions' => [
        'asset' => StandardDefinitions::class,
        'brand' => StandardDefinitions::class,
        'collection' => StandardDefinitions::class,
        'product' => StandardDefinitions::class,
        'product-option' => StandardDefinitions::class,
        'product-option-value' => StandardDefinitions::class,
        'product_type' => StandardDefinitions::class,
    ],

    'collection' => 'images',

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
