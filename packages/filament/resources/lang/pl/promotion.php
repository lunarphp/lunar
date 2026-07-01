<?php

return [

    'label' => 'Promocja',

    'plural_label' => 'Promocje',

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'starts_at' => [
            'label' => 'Data rozpoczęcia',
        ],
        'ends_at' => [
            'label' => 'Data zakończenia',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'discounts_count' => [
            'label' => 'Liczba rabatów',
        ],
        'starts_at' => [
            'label' => 'Data rozpoczęcia',
        ],
        'ends_at' => [
            'label' => 'Data zakończenia',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Rabaty',
            'description' => 'Rabaty należące do tej kampanii.',
            'actions' => [
                'associate' => [
                    'label' => 'Powiąż rabat',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'handle' => [
                    'label' => 'Slug',
                ],
                'status' => [
                    'label' => 'Status',
                ],
                'starts_at' => [
                    'label' => 'Data rozpoczęcia',
                ],
                'ends_at' => [
                    'label' => 'Data zakończenia',
                ],
            ],
        ],
    ],

];
