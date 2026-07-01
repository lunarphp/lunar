<?php

return [

    'label' => 'Promóció',

    'plural_label' => 'Promóciók',

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'description' => [
            'label' => 'Leírás',
        ],
        'starts_at' => [
            'label' => 'Kezdés dátuma',
        ],
        'ends_at' => [
            'label' => 'Befejezés dátuma',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'discounts_count' => [
            'label' => 'Kedvezmények száma',
        ],
        'starts_at' => [
            'label' => 'Kezdés dátuma',
        ],
        'ends_at' => [
            'label' => 'Befejezés dátuma',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Kedvezmények',
            'description' => 'Az ehhez a kampányhoz tartozó kedvezmények.',
            'actions' => [
                'associate' => [
                    'label' => 'Kedvezmény hozzárendelése',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'handle' => [
                    'label' => 'Azonosító',
                ],
                'status' => [
                    'label' => 'Státusz',
                ],
                'starts_at' => [
                    'label' => 'Kezdés dátuma',
                ],
                'ends_at' => [
                    'label' => 'Befejezés dátuma',
                ],
            ],
        ],
    ],

];
