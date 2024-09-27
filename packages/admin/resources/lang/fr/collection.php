<?php

return [

    'label' => 'Collection',

    'plural_label' => 'Collections',

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Collections enfants',
            'actions' => [
                'create_child' => [
                    'label' => 'Créer une collection enfant',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Nbre d\'enfants',
                ],
                'name' => [
                    'label' => 'Nom',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Informations de base',
        ],
        'products' => [
            'label' => 'Produits',
            'actions' => [
                'attach' => [
                    'label' => 'Associer un produit',
                ],
            ],
        ],
    ],

];
