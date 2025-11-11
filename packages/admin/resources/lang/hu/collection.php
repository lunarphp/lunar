<?php

return [

    'label' => 'Gyűjtemény',

    'plural_label' => 'Gyűjtemények',

    'form' => [
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
