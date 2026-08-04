<?php

use Lunar\Core\Models\Product;

return [
    /*
    |--------------------------------------------------------------------------
    | Max facet values
    |--------------------------------------------------------------------------
    |
    | The maximum number of values returned per facet. Typesense defaults to
    | 10 and caps this at 250; deep hierarchical facets (e.g. category trees)
    | may need more than the default.
    |
    */
    'max_facet_values' => 50,

    'facets' => [
        Product::class => [
            'brand' => [],
            //            'size' => [],
            //            'colour' => [
            //                'Red' => [
            //                    'hex_value' => '#FF0000',
            //                ],
            //            ],
        ],
    ],
];
