<?php

return [
    'label_plural' => 'Méthodes d\'expédition',
    'label' => 'Méthode d\'expédition',
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'cutoff' => [
            'label' => 'Date limite',
        ],
        'charge_by' => [
            'label' => 'Facturer par',
            'options' => [
                'cart_total' => 'Total du panier',
                'weight' => 'Poids',
            ],
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Collecte',
            ],
        ],
        'stock_available' => [
            'label' => 'Le stock de tous les articles du panier doit être disponible',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'driver' => [
            'label' => 'Type',
            'options' => [
                'ship-by' => 'Standard',
                'collection' => 'Collecte',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilité',
            'customer_groups' => 'Cette méthode d\'expédition est actuellement indisponible pour tous les groupes de clients.',
        ],
    ],
];
