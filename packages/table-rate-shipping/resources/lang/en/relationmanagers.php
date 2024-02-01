<?php

return [
    'shipping_rates' => [
        'title_plural' => 'Shipping Rates',
        'actions' => [
            'create' => [
                'label' => 'Create Shipping Rate',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'All prices include tax, which will be considered when calculating minimum spend.',
            'prices_excl_tax' => 'All prices exclude tax, the minimum spend will be based on the cart sub total.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Shipping Method',
            ],
            'price' => [
                'label' => 'Price',
            ],
            'prices' => [
                'label' => 'Quantity Breaks',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Customer Group',
                        'placeholder' => 'Any',
                    ],
                    'currency_id' => [
                        'label' => 'Currency',
                    ],
                    'min_quantity' => [
                        'label' => 'Min. Spend',
                    ],
                    'price' => [
                        'label' => 'Price',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Shipping Method',
            ],
            'price' => [
                'label' => 'Price',
            ],
            'price_breaks_count' => [
                'label' => 'Price Breaks',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Shipping Exclusions',
        'form' => [
            'purchasable' => [
                'label' => 'Product',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Add shipping exclusion list',
            ],
            'attach' => [
                'label' => 'Add exclusion list',
            ],
            'detach' => [
                'label' => 'Remove',
            ],
        ],
    ],
];
