<?php

return [
    'label' => 'Gyűjtemény',
    'plural_label' => 'Gyűjtemények',
    'form' => [

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
        ],
        'name' => [
            'label' => 'Név',
        ],
    ],
    'pages' => [
        'children' => [
            'label' => 'Algyűjtemények',
            'actions' => [
                'create_child' => [
                    'label' => 'Algyűjtemény létrehozása',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Gyermekek száma',
                ],
                'name' => [
                    'label' => 'Név',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Alapvető információk',
        ],
        'products' => [
            'label' => 'Termékek',
            'actions' => [
                'attach' => [
                    'label' => 'Termék csatolása',
                ],
            ],
        ],
    ],
];
