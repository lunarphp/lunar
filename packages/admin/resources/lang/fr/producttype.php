<?php

return [
    'label' => 'Type de produit',
    'plural_label' => 'Types de produits',
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'products_count' => [
            'label' => ' Nombre de produits',
        ],
        'product_attributes_count' => [
            'label' => 'Attributs de produit',
        ],
        'variant_attributes_count' => [
            'label' => 'Attributs de variants',
        ],
    ],
    'tabs' => [
        'product_attributes' => [
            'label' => 'Attributs de produit',
        ],
        'variant_attributes' => [
            'label' => 'Attributs de variants',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
    ],
    'attributes' => [
        'no_groups' => 'Il n\'y a pas de groupes d\'attributs disponibles.',
        'no_attributes' => 'Il n\'y a pas d\'attributs disponibles.',
    ],
    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ce type de produit ne peux être supprimé car il y a des produits associés.',
            ],
        ],
    ],
];
