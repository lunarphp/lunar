<?php

return [
    'label' => 'Kolekcja',
    'plural_label' => 'Kolekcje',
    'form' => [

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
        ],
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
