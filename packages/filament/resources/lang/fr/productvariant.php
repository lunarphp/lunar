<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
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
        'continue_selling' => [
            'label' => 'Continuer à vendre en rupture de stock',
            'tooltip' => 'Activé, cette variante peut être commandée quel que soit le stock. Désactivé, les clients ne peuvent acheter que le stock disponible.',
        ],
        'sell_against_incoming' => [
            'label' => 'Vendre sur le stock entrant',
            'tooltip' => 'Compte le stock entrant comme disponible, afin que les clients puissent commander les unités attendues.',
        ],
        'unit_quantity' => [
            'label' => 'Quantité par unité',
            'tooltip' => 'Combien d\'articles individuels composent 1 unité.',
        ],
        'min_quantity' => [
            'label' => 'Quantité minimale',
            'tooltip' => 'La quantité minimale d\'une variante de produit qui peut être achetée en un seul achat.',
        ],
        'quantity_increment' => [
            'label' => 'Incrément de quantité',
            'tooltip' => 'La variante de produit doit être achetée en multiples de cette quantité.',
        ],
        'tax_class_id' => [
            'label' => 'Classe de taxe',
        ],
        'shippable' => [
            'label' => 'Expédiable',
        ],
        'fulfilment_methods' => [
            'label' => 'Méthodes de traitement',
            'helper_text' => 'Comment cette variante est traitée. Cocher une méthode physique, comme l\'expédition ou le retrait, la rend expédiable. Ne cochez rien s\'il n\'y a rien à traiter, comme pour un téléchargement immédiat.',
            'unavailable' => ':methods n\'est pas disponible pour le moment, les clients ne peuvent donc pas le choisir. Décochez-le ou rendez-le à nouveau disponible.',
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
