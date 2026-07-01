<?php

return [

    'label' => 'Promotion',

    'plural_label' => 'Promotions',

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'starts_at' => [
            'label' => 'Starts At',
        ],
        'ends_at' => [
            'label' => 'Ends At',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'discounts_count' => [
            'label' => 'No. Discounts',
        ],
        'starts_at' => [
            'label' => 'Starts At',
        ],
        'ends_at' => [
            'label' => 'Ends At',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Discounts',
            'description' => 'The discounts that belong to this campaign.',
            'actions' => [
                'associate' => [
                    'label' => 'Associate a discount',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'handle' => [
                    'label' => 'Handle',
                ],
                'status' => [
                    'label' => 'Status',
                ],
                'starts_at' => [
                    'label' => 'Starts At',
                ],
                'ends_at' => [
                    'label' => 'Ends At',
                ],
            ],
        ],
    ],

];
