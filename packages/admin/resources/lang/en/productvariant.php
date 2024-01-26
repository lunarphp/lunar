<?php

return [
    'label' => 'Product Variant',
    'plural_label' => 'Product Variants',
    'pages' => [
        'edit' => [
            'title' => 'Basic Information',
        ],
        'identifiers' => [
            'title' => 'Identifiers',
        ],
        'inventory' => [
            'title' => 'Inventory',
        ],
        'shipping' => [
            'title' => 'Shipping',
        ],
    ],
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
        'unit_quantity' => [
            'label' => 'Unit Quantity',
            'helper_text' => 'How many individual items make up 1 unit.',
        ],
        'min_quantity' => [
            'label' => 'Minimum Quantity',
            'helper_text' => 'The minimum quantity of a product variant that can be bought in a single purchase.',
        ],
        'quantity_increment' => [
            'label' => 'Quantity Increment',
            'helper_text' => 'The product variant must be purchased in multiples of this quantity.',
        ],
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
];
