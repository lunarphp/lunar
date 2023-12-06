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
        'inventory' => [
            'label' => 'Inventory',
            'form' => [
                'stock' => [
                    'label' => 'In Stock',
                ],
                'backorder' => [
                    'label' => 'On Backorder',
                ],
                'purchasable' => [
                    'label' => 'Purchasability',
                    'options' => [
                        'always' => 'Always',
                        'in_stock' => 'In Stock',
                        'backorder' => 'Backorder Only',
                    ],
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Shipping',
            'form' => [
                'shippable' => [
                    'label' => 'Shippable',
                ],
                'length_value' => [
                    'label' => 'Length',
                ],
                'length_unit' => [
                    'label' => 'Length Unit',
                ],
                'width_value' => [
                    'label' => 'Width',
                ],
                'width_unit' => [
                    'label' => 'Width Unit',
                ],
                'height_value' => [
                    'label' => 'Height',
                ],
                'height_unit' => [
                    'label' => 'Height Unit',
                ],
                'weight_value' => [
                    'label' => 'Weight',
                ],
                'weight_unit' => [
                    'label' => 'Weight Unit',
                ],
            ],
        ],
    ],

];
