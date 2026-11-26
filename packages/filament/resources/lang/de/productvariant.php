<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'incoming' => 'Eingehend',
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
        'continue_selling' => [
            'label' => 'Bei fehlendem Lagerbestand weiterverkaufen',
            'tooltip' => 'Wenn aktiviert, kann diese Variante unabhängig vom Lagerbestand bestellt werden. Wenn deaktiviert, können Kunden nur verfügbare Ware kaufen.',
        ],
        'sell_against_incoming' => [
            'label' => 'Gegen eingehenden Lagerbestand verkaufen',
            'tooltip' => 'Eingehenden Lagerbestand als verfügbar zählen, damit Kunden erwartete Einheiten bestellen können.',
        ],
        'unit_quantity' => [
            'label' => 'Stückzahl',
            'tooltip' => 'Wie viele Einzelartikel 1 Einheit ausmachen.',
        ],
        'min_quantity' => [
            'label' => 'Mindestmenge',
            'tooltip' => 'Die Mindestmenge einer Produktvariante, die in einem einzigen Kauf erworben werden kann.',
        ],
        'quantity_increment' => [
            'label' => 'Mengeninkrement',
            'tooltip' => 'Die Produktvariante muss in Vielfachen dieser Menge gekauft werden.',
        ],
        'tax_class_id' => [
            'label' => 'Steuerklasse',
        ],
        'shippable' => [
            'label' => 'Versandfähig',
        ],
        'fulfilment_methods' => [
            'label' => 'Fulfillment-Methoden',
            'helper_text' => 'Wie diese Variante erfüllt wird. Eine physische Methode wie Versand oder Abholung macht sie versandfähig. Haken Sie nichts an, wenn es nichts zu erfüllen gibt, etwa bei einem Sofort-Download.',
            'unavailable' => ':methods ist derzeit nicht verfügbar und kann von Kunden nicht gewählt werden. Entfernen Sie den Haken oder machen Sie die Methode wieder verfügbar.',
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
