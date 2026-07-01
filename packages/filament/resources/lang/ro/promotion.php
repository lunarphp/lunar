<?php

return [

    'label' => 'Promoție',

    'plural_label' => 'Promoții',

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'description' => [
            'label' => 'Descriere',
        ],
        'starts_at' => [
            'label' => 'Data de început',
        ],
        'ends_at' => [
            'label' => 'Data de sfârșit',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'discounts_count' => [
            'label' => 'Nr. reduceri',
        ],
        'starts_at' => [
            'label' => 'Data de început',
        ],
        'ends_at' => [
            'label' => 'Data de sfârșit',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Reduceri',
            'description' => 'Reducerile care aparțin acestei campanii.',
            'actions' => [
                'associate' => [
                    'label' => 'Asociază o reducere',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'handle' => [
                    'label' => 'Identificator',
                ],
                'status' => [
                    'label' => 'Stare',
                ],
                'starts_at' => [
                    'label' => 'Data de început',
                ],
                'ends_at' => [
                    'label' => 'Data de sfârșit',
                ],
            ],
        ],
    ],

];
