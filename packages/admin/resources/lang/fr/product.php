<?php

return [
    'label' => 'Produit',
    'plural_label' => 'Produits',
    'status' => [
        'unpublished' => [
            'content' => 'Actuellement en statut de brouillon, ce produit est caché sur toutes les plateformes et pour tous les groupes de clients.',
        ],
        'availability' => [
            'customer_groups' => 'Ce produit est actuellement indisponible pour tous les groupes de clients.',
            'channels' => 'Ce produit est actuellement indisponible sur toutes les plateformes.',
        ],
    ],
    'table' => [
        'status' => [
            'label' => 'Statut',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'brand' => [
            'label' => 'Marque',
        ],
        'sku' => [
            'label' => 'Référence',
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
            'label' => 'Référence',
        ],
        'producttype' => [
            'label' => 'Type de produit',
        ],
        'status' => [
            'label' => 'Statut',
            'options' => [
                'published' => [
                    'label' => 'Publié',
                    'description' => 'Ce produit sera disponible sur toutes les groupes de clients et chaînes activées',
                ],
                'draft' => [
                    'label' => 'Brouillon',
                    'description' => 'Ce produit sera caché sur toutes les plateformes et pour tous les groupes de clients',
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
        'media' => [
            'label' => 'Médias',
        ],
        'identifiers' => [
            'label' => 'Identifiants de produit',
        ],
        'inventory' => [
            'label' => 'Stock',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Classe d\'impôt',
                ],
                'tax_ref' => [
                    'label' => 'Référence d\'imposition',
                    'helper_text' => 'Facultatif, pour l\'intégration avec des systèmes tiers.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Expédition',
        ],
    ],
];
