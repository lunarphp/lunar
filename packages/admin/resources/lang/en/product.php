<?php

return [

    'label' => 'Product',

    'plural_label' => 'Products',

    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'brand' => [
            'label' => 'Brand',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Stock',
        ],
        'producttype' => [
            'label' => 'Product Type',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'brand' => [
            'label' => 'Brand',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Product Type',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'collections' => [
            'label' => 'Collections',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Availability',
        ],
        'media' => [
            'label' => 'Media',
        ],
        'identifiers' => [
            'label' => 'Product Identifiers',
            'form' => [
                'sku' => [
                    'label' => 'SKU',
                ],
                'gtin' => [
                    'label' => 'Global Trade Item Number (GTIN)',
                ],
                'mpn' => [
                    'label' => 'Manufacturer Part Number (MPN)',
                ],
                'ean' => [
                    'label' => 'UPC/EAN',
                ],
            ],
        ],
    ],

];
