<?php

return [

    'label' => 'Promocija',

    'plural_label' => 'Promocije',

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'starts_at' => [
            'label' => 'Datum početka',
        ],
        'ends_at' => [
            'label' => 'Datum završetka',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'discounts_count' => [
            'label' => 'Broj popusta',
        ],
        'starts_at' => [
            'label' => 'Datum početka',
        ],
        'ends_at' => [
            'label' => 'Datum završetka',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Popusti',
            'description' => 'Popusti koji pripadaju ovoj kampanji.',
            'actions' => [
                'associate' => [
                    'label' => 'Poveži popust',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'handle' => [
                    'label' => 'Identifikator',
                ],
                'status' => [
                    'label' => 'Status',
                ],
                'starts_at' => [
                    'label' => 'Datum početka',
                ],
                'ends_at' => [
                    'label' => 'Datum završetka',
                ],
            ],
        ],
    ],

];
