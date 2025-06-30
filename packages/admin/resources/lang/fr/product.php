<?php

return [

    'label' => 'Produit',

    'plural_label' => 'Produits',

    'tabs' => [
        'all' => 'Tous',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Actuellement en statut de brouillon, ce produit est masqué sur tous les canaux et groupes de clients.',
        ],
        'availability' => [
            'customer_groups' => 'Ce produit est actuellement indisponible pour tous les groupes de clients.',
            'channels' => 'Ce produit est actuellement indisponible pour tous les canaux.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Statut',
            'states' => [
                'deleted' => 'Supprimé',
                'draft' => 'Brouillon',
                'published' => 'Publié',
            ],
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'brand' => [
            'label' => 'Marque',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Stock',
        ],
        'producttype' => [
            'label' => 'Type de produit',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Mettre à jour le statut',
            'heading' => 'Mettre à jour le statut',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'brand' => [
            'label' => 'Marque',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Type de produit',
        ],
        'status' => [
            'label' => 'Statut',
            'options' => [
                'published' => [
                    'label' => 'Publié',
                    'description' => 'Ce produit sera disponible pour tous les groupes de clients et canaux activés',
                ],
                'draft' => [
                    'label' => 'Brouillon',
                    'description' => 'Ce produit sera masqué sur tous les canaux et groupes de clients',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Étiquettes',
        ],
        'collections' => [
            'label' => 'Collections',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Disponibilité',
        ],
        'edit' => [
            'title' => 'Informations de base',
        ],
        'identifiers' => [
            'label' => 'Identifiants du produit',
        ],
        'inventory' => [
            'label' => 'Inventaire',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Classe de taxe',
                ],
                'tax_ref' => [
                    'label' => 'Référence de taxe',
                    'helper_text' => 'Optionnel, pour l\'intégration avec des systèmes tiers.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Expédition',
        ],
        'variants' => [
            'label' => 'Variantes',
        ],
        'collections' => [
            'label' => 'Collections',
            'select_collection' => 'Sélectionner une collection',
        ],
        'associations' => [
            'label' => 'Associations de produits',
        ],
    ],

];
