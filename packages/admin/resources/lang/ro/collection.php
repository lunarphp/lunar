<?php

return [

    'label' => 'Colecție',

    'plural_label' => 'Colecții',

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Colecții copil',
            'actions' => [
                'create_child' => [
                    'label' => 'Creează colecție copil',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Nr. copii',
                ],
                'name' => [
                    'label' => 'Nume',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Informații de bază',
        ],
        'products' => [
            'label' => 'Produse',
            'actions' => [
                'attach' => [
                    'label' => 'Atașează produs',
                ],
            ],
        ],
    ],

];
