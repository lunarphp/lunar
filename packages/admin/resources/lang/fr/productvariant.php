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
                    'label' => 'Aucun média n\'est actuellement disponible pour ce produit.',
                ],
                'images' => [
                    'label' => 'Image principale',
                    'helper_text' => 'Sélectionnez l\'image du produit qui représente cette variante.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identifiants',
        ],
        'inventory' => [
            'title' => 'Inventaire',
        ],
        'shipping' => [
            'title' => 'Expédition',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Numéro global de l\'article commercial (GTIN)',
        ],
        'mpn' => [
            'label' => 'Numéro de pièce du fabricant (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'label' => 'En stock',
        ],
        'backorder' => [
            'label' => 'En commande',
        ],
        'purchasable' => [
            'label' => 'Achat possible',
            'options' => [
                'always' => 'Toujours',
                'in_stock' => 'En stock',
                'in_stock_or_on_backorder' => 'En stock ou en commande',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Quantité par unité',
            'helper_text' => 'Combien d\'articles individuels composent 1 unité.',
        ],
        'min_quantity' => [
            'label' => 'Quantité minimale',
            'helper_text' => 'La quantité minimale d\'une variante de produit qui peut être achetée en un seul achat.',
        ],
        'quantity_increment' => [
            'label' => 'Incrément de quantité',
            'helper_text' => 'La variante de produit doit être achetée en multiples de cette quantité.',
        ],
        'tax_class_id' => [
            'label' => 'Classe de taxe',
        ],
        'shippable' => [
            'label' => 'Expédiable',
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
            'label' => 'Unité de hauteur',
        ],
        'weight_value' => [
            'label' => 'Poids',
        ],
        'weight_unit' => [
            'label' => 'Unité de poids',
        ],
    ],
];
