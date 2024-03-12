<?php

return [
    'label' => 'Variante de produit',
    'plural_label' => 'Variantes de produit',
    'pages' => [
        'edit' => [
            'title' => 'Informations de base',
        ],
        'media' => [
            'title' => 'Médias',
            'form' => [
                'no_selection' => [
                    'label' => 'Vous n\'avez actuellement aucune image sélectionnée pour cette variante.',
                ],
                'no_media_available' => [
                    'label' => 'Il n\'y a actuellement aucun média disponible sur ce produit.',
                ],
                'images' => [
                    'label' => 'Image principale',
                    'helper_text' => 'Sélectionner l\'image de produit qui représente cette variante.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identifiants',
        ],
        'inventory' => [
            'title' => 'Stock',
        ],
        'shipping' => [
            'title' => 'Expédition',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'REF',
        ],
        'gtin' => [
            'label' => 'Numéro d\'identification commerciale mondial (GTIN)',
        ],
        'mpn' => [
            'label' => 'Référence du fabricant (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'label' => 'En stock',
        ],
        'backorder' => [
            'label' => 'en commande',
            'options' => [
                0 => 'disponible',
                1 => 'en_commande',
            ],
        ],
        'purchasable' => [
            'label' => 'Achats autorisés',
            'options' => [
                'always' => 'Toujours',
                'in_stock' => 'En stock',
                'backorder' => 'Commande uniquement',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Quantité par unité',
            'helper_text' => 'Combien de produits individuels font une unité.',
        ],
        'min_quantity' => [
            'label' => 'Quantité minimale',
            'helper_text' => 'La quantité minimale de cette variante de produit que l\'on peut acheter dans une seule commande.',
        ],
        'quantity_increment' => [
            'label' => 'Incrément de quantité',
            'helper_text' => 'Cette variété de produit doit être achetée en multiples de cette quantité.',
        ],
        'tax_class_id' => [
            'label' => 'Classe d\'impôt',
        ],
        'shippable' => [
            'label' => 'Expédable',
        ],
        'length_value' => [
            'label' => 'Longueur',
        ],
        'length_unit' => [
            'label' => 'Unité de longueur',
        ],
        'width_value' => [
            'label' => 'Largeur',
        ],
        'width_unit' => [
            'label' => 'Unité de largeur',
        ],
        'height_value' => [
            'label' => 'Hauteur',
        ],
        'height_unit' => [
            'label' => 'Unité d\'altitude',
        ],
        'weight_value' => [
            'label' => 'Poids',
        ],
        'weight_unit' => [
            'label' => 'Unité de poids',
        ],
    ],
];
