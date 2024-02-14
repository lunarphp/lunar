<?php

return [
    'customer_groups' => [
        'actions' => [
            'attach' => [
                'label' => 'Attach Customer Group',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Name',
            ],
            'enabled' => [
                'label' => 'Enabled',
            ],
            'starts_at' => [
                'label' => 'Start Date',
            ],
            'ends_at' => [
                'label' => 'End Date',
            ],
            'visible' => [
                'label' => 'Visible',
            ],
            'purchasable' => [
                'label' => 'Purchasable',
            ],
        ],
        'table' => [
            'description' => 'Associate customer groups to this product to determine it\'s availability.',
            'name' => [
                'label' => 'Name',
            ],
            'enabled' => [
                'label' => 'Enabled',
            ],
            'starts_at' => [
                'label' => 'Start Date',
            ],
            'ends_at' => [
                'label' => 'End Date',
            ],
            'visible' => [
                'label' => 'Visible',
            ],
            'purchasable' => [
                'label' => 'Purchasable',
            ],
        ],
    ],
    'channels' => [
        'actions' => [
            'attach' => [
                'label' => 'Schedule another Channel',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Enabled',
                'helper_text_false' => 'This channel will not be enabled even if a start date is present.',
            ],
            'starts_at' => [
                'label' => 'Start Date',
                'helper_text' => 'Leave blank to be available from any date.',
            ],
            'ends_at' => [
                'label' => 'End Date',
                'helper_text' => 'Leave blank to be available indefinitely.',
            ],
        ],
        'table' => [
            'description' => 'Determine which channels are enabled and schedule the availability.',
            'name' => [
                'label' => 'Name',
            ],
            'enabled' => [
                'label' => 'Enabled',
            ],
            'starts_at' => [
                'label' => 'Start Date',
            ],
            'ends_at' => [
                'label' => 'End Date',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'Create URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Language',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Default',
            ],
            'language' => [
                'label' => 'Language',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Default',
            ],
            'language' => [
                'label' => 'Language',
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Pricing',
        'title_plural' => 'Pricing',
        'table' => [
            'heading' => 'Price Breaks',
            'description' => 'Reduce the price when a customer purchases in larger quantities.',
            'empty_state' => [
                'label' => 'No price breaks exist.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Add Price Break',
                ],
            ],
            'price' => [
                'label' => 'Price',
            ],
            'customer_group' => [
                'label' => 'Customer Group',
                'placeholder' => 'All Customer Groups',
            ],
            'quantity_break' => [
                'label' => 'Quantity Break',
            ],
            'currency' => [
                'label' => 'Currency',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Price',
                'helper_text' => 'The purchase price, before discounts.',
            ],
            'customer_group_id' => [
                'label' => 'Customer Group',
                'placeholder' => 'All Customer Groups',
                'helper_text' => 'Select which customer group to apply this price to.',
            ],
            'quantity_break' => [
                'label' => 'Quantity Break',
                'helper_text' => 'Select the minimum quantity this price will be available for.',
            ],
            'currency_id' => [
                'label' => 'Currency',
                'helper_text' => 'Select the currency for this price.',
            ],
            'compare_price' => [
                'label' => 'Comparison Price',
                'helper_text' => 'The original price or RRP, for comparison with its purchase price.',
            ],
            'basePrices' => [
                'title' => 'Prices',
                'form' => [
                    'price' => [
                        'label' => 'Price',
                        'helper_text' => 'The purchase price, before discounts.',
                    ],
                    'compare_price' => [
                        'label' => 'Comparison Price',
                        'helper_text' => 'The original price or RRP, for comparison with its purchase price.',
                    ],
                ],
                'tooltip' => 'Automatically generated based on currency exchange rates.',
            ],
        ],
    ],
];
