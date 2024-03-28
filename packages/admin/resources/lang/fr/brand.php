<?php

return [
    'label' => 'Marque',
    'plural_label' => 'Marques',
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'products_count' => [
            'label' => 'Nb Produits',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
    ],
    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Cette marque ne pouvant être supprimée car il y a des produits associés.',
            ],
        ],
    ],
];
