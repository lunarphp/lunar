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

    // Maximum accepted upload size per image, in kilobytes. Drives both the
    // upload validation rules and the size shown in the panel's uploader hint.
    'max_upload_kb' => 8192,

    'fallback' => [
        'url' => env('FALLBACK_IMAGE_URL', null),
        'path' => env('FALLBACK_IMAGE_PATH', null),
    ],

];
