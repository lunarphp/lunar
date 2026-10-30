<?php

use Lunar\Core\Models\Product;

return [
    'typesense' => [
        /*
         * Values returned per facet. Typesense returns only the most frequent
         * values, so a facet with more distinct values than this (a category
         * hierarchy, say) silently drops the smallest. Typesense's own default
         * is 10.
         */
        'max_facet_values' => 50,
    ],

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
