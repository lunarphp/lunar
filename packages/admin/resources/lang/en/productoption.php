<?php

return [

    'label' => 'Product Option',

    'plural_label' => 'Product Options',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'label' => [
            'label' => 'Label',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'label' => [
            'label' => 'Label',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'options-table' => [
                'title' => 'Product Options',
                'configure-options' => [
                    'label' => 'Configure Options',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Option',
                    ],
                    'values' => [
                        'label' => 'Values',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Product Variants',
                'table' => [
                    'new' => [
                        'label' => 'NEW',
                    ],
                    'option' => [
                        'label' => 'Option',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Price',
                    ],
                    'stock' => [
                        'label' => 'Stock',
                    ],
                ],
            ],
        ],
    ],

];
