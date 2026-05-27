<?php

return [
    'label' => 'Sammlung',
    'plural_label' => 'Sammlungen',
    'form' => [

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
        ],
        'name' => [
            'label' => 'Name',
        ],
    ],
    'pages' => [
        'children' => [
            'label' => 'Untergeordnete Sammlungen',
            'actions' => [
                'create_child' => [
                    'label' => 'Untergeordnete Sammlung erstellen',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Anzahl der Untergeordneten',
                ],
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Grundinformation',
        ],
        'products' => [
            'label' => 'Produkte',
            'actions' => [
                'attach' => [
                    'label' => 'Produkt zuordnen',
                ],
            ],
        ],
    ],
];
