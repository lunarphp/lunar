<?php

return [

    'label' => 'Kolekcja',

    'plural_label' => 'Kolekcje',

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Podkolekcje',
            'actions' => [
                'create_child' => [
                    'label' => 'Dodaj podkolekcję',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Liczba podkolekcji',
                ],
                'name' => [
                    'label' => 'Nazwa',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Podstawowe informacje',
        ],
        'media' => [
            'label' => 'Media',
        ],
        'products' => [
            'label' => 'Produkty',
            'actions' => [
                'attach' => [
                    'label' => 'Dołącz produkt',
                ],
            ],
        ],
    ],

];
