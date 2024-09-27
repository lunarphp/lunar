<?php

return [

    'label' => 'Type de produit',

    'plural_label' => 'Types de produit',

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'products_count' => [
            'label' => 'Nombre de produits',
        ],
        'product_attributes_count' => [
            'label' => 'Attributs de produit',
        ],
        'variant_attributes_count' => [
            'label' => 'Attributs de variante',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Attributs de produit',
        ],
        'variant_attributes' => [
            'label' => 'Attributs de variante',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Aucun groupe d\'attributs disponible.',
        'no_attributes' => 'Aucun attribut disponible.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ce type de produit ne peut pas être supprimé car des produits y sont associés.',
            ],
        ],
    ],

];
