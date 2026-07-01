<?php

return [

    'label' => 'Aktion',

    'plural_label' => 'Aktionen',

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'description' => [
            'label' => 'Beschreibung',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Enddatum',
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
            'label' => 'Anzahl Rabatte',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Enddatum',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Rabatte',
            'description' => 'Die Rabatte, die zu dieser Aktion gehören.',
            'actions' => [
                'associate' => [
                    'label' => 'Rabatt zuordnen',
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
                    'label' => 'Startdatum',
                ],
                'ends_at' => [
                    'label' => 'Enddatum',
                ],
            ],
        ],
    ],

];
