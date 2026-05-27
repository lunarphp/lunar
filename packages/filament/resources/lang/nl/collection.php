<?php

return [
    'label' => 'Collectie',
    'plural_label' => 'Collecties',
    'form' => [

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
        ],
        'name' => [
            'label' => 'Naam',
        ],
    ],
    'pages' => [
        'children' => [
            'label' => 'Subcollecties',
            'actions' => [
                'create_child' => [
                    'label' => 'Maak Subcollectie',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Aantal Kinderen',
                ],
                'name' => [
                    'label' => 'Naam',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Basisinformatie',
        ],
        'products' => [
            'label' => 'Producten',
            'actions' => [
                'attach' => [
                    'label' => 'Product Toevoegen',
                ],
            ],
        ],
    ],
];
