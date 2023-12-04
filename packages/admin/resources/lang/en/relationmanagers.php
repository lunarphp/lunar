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
            'price' => [
                'label' => 'Price',
            ],
            'customer_group' => [
                'label' => 'Customer Group',
            ],
            'tier' => [
                'label' => 'Tier',
            ],
            'currency' => [
                'label' => 'Currency',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Price',
            ],
            'customer_group_id' => [
                'label' => 'Customer Group',
            ],
            'tier' => [
                'label' => 'Tier',
            ],
            'currency_id' => [
                'label' => 'Currency',
            ],
        ],
    ],
];
