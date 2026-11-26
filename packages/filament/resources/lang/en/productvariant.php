<?php

return [
    'label' => 'Product Variant',
    'plural_label' => 'Product Variants',
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'Incoming',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
    'pages' => [
        'edit' => [
            'title' => 'Basic Information',
        ],
        'media' => [
            'title' => 'Media',
            'form' => [
                'no_selection' => [
                    'label' => 'You do not currently have an image selected for this variant.',
                ],
                'no_media_available' => [
                    'label' => 'There is currently no media available on this product.',
                ],
                'images' => [
                    'label' => 'Primary Image',
                    'helper_text' => 'Select the product image which represents this variant.',
                ],
            ],
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
        'continue_selling' => [
            'label' => 'Continue Selling When Out of Stock',
            'tooltip' => 'When on, this variant can be ordered regardless of stock. When off, customers can only buy what is available.',
        ],
        'sell_against_incoming' => [
            'label' => 'Sell Against Incoming Stock',
            'tooltip' => 'Count incoming stock as available, so customers can order units you are expecting.',
        ],
        'unit_quantity' => [
            'label' => 'Unit Quantity',
            'tooltip' => 'How many individual items make up 1 unit.',
        ],
        'min_quantity' => [
            'label' => 'Minimum Quantity',
            'tooltip' => 'The minimum quantity of a product variant that can be bought in a single purchase.',
        ],
        'quantity_increment' => [
            'label' => 'Quantity Increment',
            'tooltip' => 'The product variant must be purchased in multiples of this quantity.',
        ],
        'tax_class_id' => [
            'label' => 'Tax Class',
        ],
        'shippable' => [
            'label' => 'Shippable',
        ],
        'fulfilment_methods' => [
            'label' => 'Fulfilment methods',
            'helper_text' => 'How this variant is fulfilled. Ticking a physical method such as shipping or pickup makes it shippable. Tick none when there is nothing to fulfil, such as an instant download.',
            'unavailable' => ':methods isn\'t available right now, so customers can\'t choose it. Untick it, or make it available again.',
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
