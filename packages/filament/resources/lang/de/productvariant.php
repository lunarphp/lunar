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
    'label' => 'Produktvariante',
    'plural_label' => 'Produktvarianten',
    'pages' => [
        'edit' => [
            'title' => 'Grundinformationen',
        ],
        'media' => [
            'title' => 'Medien',
            'form' => [
                'no_selection' => [
                    'label' => 'Derzeit ist kein Bild für diese Variante ausgewählt.',
                ],
                'no_media_available' => [
                    'label' => 'Derzeit sind keine Medien für dieses Produkt verfügbar.',
                ],
                'images' => [
                    'label' => 'Hauptbild',
                    'helper_text' => 'Wählen Sie das Produktbild, das diese Variante darstellt.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Kennungen',
        ],
        'inventory' => [
            'title' => 'Inventar',
        ],
        'shipping' => [
            'title' => 'Versand',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Globale Handelsartikelnummer (GTIN)',
        ],
        'mpn' => [
            'label' => 'Teilenummer des Herstellers (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'tooltip' => 'Units on hand at your default location. Changing this records a stock adjustment.',
            'label' => 'Auf Lager',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'Im Rückstand',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'Immer',
                'in_stock' => 'Auf Lager',
                'in_stock_or_on_backorder' => 'Auf Lager oder im Rückstand',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Stückzahl',
            'helper_text' => 'Wie viele Einzelartikel 1 Einheit ausmachen.',
        ],
        'min_quantity' => [
            'label' => 'Mindestmenge',
            'helper_text' => 'Die Mindestmenge einer Produktvariante, die in einem einzigen Kauf erworben werden kann.',
        ],
        'quantity_increment' => [
            'label' => 'Mengeninkrement',
            'helper_text' => 'Die Produktvariante muss in Vielfachen dieser Menge gekauft werden.',
        ],
        'tax_class_id' => [
            'label' => 'Steuerklasse',
        ],
        'shippable' => [
            'label' => 'Versandfähig',
        ],
        'length_value' => [
            'label' => 'Länge',
        ],
        'length_unit' => [
            'label' => 'Längeneinheit',
        ],
        'width_value' => [
            'label' => 'Breite',
        ],
        'width_unit' => [
            'label' => 'Breiteneinheit',
        ],
        'height_value' => [
            'label' => 'Höhe',
        ],
        'height_unit' => [
            'label' => 'Höheneinheit',
        ],
        'weight_value' => [
            'label' => 'Gewicht',
        ],
        'weight_unit' => [
            'label' => 'Gewichtseinheit',
        ],
    ],
];
