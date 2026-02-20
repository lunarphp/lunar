<?php

return [
    'customer_groups' => [
        'title' => 'Customer Groups',
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
            'description' => 'Associate customer groups to this :type to determine it\'s availability.',
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
        'title' => 'Channels',
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
    'medias' => [
        'title' => 'Media',
        'title_plural' => 'Media',
        'actions' => [
            'attach' => [
                'label' => 'Attach Media',
            ],
            'create' => [
                'label' => 'Create Media',
            ],
            'detach' => [
                'label' => 'Detach',
            ],
            'view' => [
                'label' => 'View',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Name',
            ],
            'media' => [
                'label' => 'Image',
            ],
            'primary' => [
                'label' => 'Primary',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Image',
            ],
            'file' => [
                'label' => 'File',
            ],
            'name' => [
                'label' => 'Name',
            ],
            'primary' => [
                'label' => 'Primary',
            ],
        ],
        'all_media_attached' => 'There are no product images available to attach',
        'variant_description' => 'Attach product images to this variant',
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
    'customer_group_pricing' => [
        'title' => 'Customer Group Pricing',
        'title_plural' => 'Customer Group Pricing',
        'table' => [
            'heading' => 'Customer Group Pricing',
            'description' => 'Associate price to customer groups to determine product price.',
            'empty_state' => [
                'label' => 'No customer group pricing exist.',
                'description' => 'Create a customer group price to get started.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Add Customer Group Price',
                    'modal' => [
                        'heading' => 'Create Customer Group Price',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Pricing',
        'title_plural' => 'Pricing',
        'tab_name' => 'Price Breaks',
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
            'min_quantity' => [
                'label' => 'Minimum Quantity',
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
            'min_quantity' => [
                'label' => 'Minimum Quantity',
                'helper_text' => 'Select the minimum quantity this price will be available for.',
                'validation' => [
                    'unique' => 'Customer Group and Minimum Quantity must be unique.',
                ],
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
                        'sync_price' => 'Price is synced with the default currency.',
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
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Percentage',
            ],
            'tax_class' => [
                'label' => 'Tax Class',
            ],
        ],
    ],
    'values' => [
        'title' => 'Values',
        'form' => [
            'name' => [
                'label' => 'Name',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Name',
            ],
            'position' => [
                'label' => 'Position',
            ],
        ],
    ],

];
