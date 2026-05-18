<?php

return [

    'label' => 'Brend',

    'plural_label' => 'Brendovi',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'products_count' => [
            'label' => 'Broj proizvoda',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ovaj brend nije moguće izbrisati jer postoje povezani proizvodi.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Osnovni podaci',
        ],
        'products' => [
            'label' => 'Proizvodi',
            'actions' => [
                'attach' => [
                    'label' => 'Pridruži proizvod',
                    'form' => [
                        'record_id' => [
                            'label' => 'Proizvod',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Proizvod pridružen brendu',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Proizvod uklonjen.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Kolekcije',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Odaberite kolekciju',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Pridruži kolekciju',
                ],
            ],
        ],
    ],

];
