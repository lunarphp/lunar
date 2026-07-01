<?php

return [

    'label' => 'Promotie',

    'plural_label' => 'Promoties',

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'description' => [
            'label' => 'Beschrijving',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Einddatum',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'discounts_count' => [
            'label' => 'Aantal Kortingen',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Einddatum',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Kortingen',
            'description' => 'De kortingen die bij deze campagne horen.',
            'actions' => [
                'associate' => [
                    'label' => 'Een korting koppelen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'handle' => [
                    'label' => 'Handle',
                ],
                'status' => [
                    'label' => 'Status',
                ],
                'starts_at' => [
                    'label' => 'Startdatum',
                ],
                'ends_at' => [
                    'label' => 'Einddatum',
                ],
            ],
        ],
    ],

];
