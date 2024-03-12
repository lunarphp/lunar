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
                    'label' => 'Nombre d\'enfants',
                ],
                'name' => [
                    'label' => 'Nom',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Informations de base',
        ],
        'media' => [
            'label' => 'Médias',
        ],
        'products' => [
            'label' => 'Produits',
            'actions' => [
                'attach' => [
                    'label' => 'Attacher produit',
                ],
            ],
        ],
    ],
];
