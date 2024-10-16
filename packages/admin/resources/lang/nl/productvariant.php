<?php

return [
    'label' => 'Productvariant',
    'plural_label' => 'Productvarianten',
    'pages' => [
        'edit' => [
            'title' => 'Basisinformatie',
        ],
        'media' => [
            'title' => 'Media',
            'form' => [
                'no_selection' => [
                    'label' => 'U heeft momenteel geen afbeelding geselecteerd voor deze variant.',
                ],
                'no_media_available' => [
                    'label' => 'Er is momenteel geen media beschikbaar voor dit product.',
                ],
                'images' => [
                    'label' => 'Primaire Afbeelding',
                    'helper_text' => 'Selecteer de productafbeelding die deze variant vertegenwoordigt.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identificatoren',
        ],
        'inventory' => [
            'title' => 'Voorraad',
        ],
        'shipping' => [
            'title' => 'Verzending',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'Artikelnummer (SKU)',
        ],
        'gtin' => [
            'label' => 'Globaal Handelsartikelnummer (GTIN)',
        ],
        'mpn' => [
            'label' => 'Fabrikant Onderdeelnummer (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'label' => 'Op Voorraad',
        ],
        'backorder' => [
            'label' => 'In Nabestelling',
        ],
        'purchasable' => [
            'label' => 'Koopbaarheid',
            'options' => [
                'always' => 'Altijd',
                'in_stock' => 'Op Voorraad',
                'in_stock_or_on_backorder' => 'Op Voorraad of In Nabestelling',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Eenheidsaantal',
            'helper_text' => 'Hoeveel individuele items vormen 1 eenheid.',
        ],
        'min_quantity' => [
            'label' => 'Minimale Hoeveelheid',
            'helper_text' => 'De minimale hoeveelheid van een productvariant die in één aankoop kan worden gekocht.',
        ],
        'quantity_increment' => [
            'label' => 'Hoeveelheidsverhoging',
            'helper_text' => 'De productvariant moet in veelvouden van deze hoeveelheid worden gekocht.',
        ],
        'tax_class_id' => [
            'label' => 'Belastingklasse',
        ],
        'shippable' => [
            'label' => 'Verzendbaar',
        ],
        'length_value' => [
            'label' => 'Lengte',
        ],
        'length_unit' => [
            'label' => 'Lengte-eenheid',
        ],
        'width_value' => [
            'label' => 'Breedte',
        ],
        'width_unit' => [
            'label' => 'Breedte-eenheid',
        ],
        'height_value' => [
            'label' => 'Hoogte',
        ],
        'height_unit' => [
            'label' => 'Hoogte-eenheid',
        ],
        'weight_value' => [
            'label' => 'Gewicht',
        ],
        'weight_unit' => [
            'label' => 'Gewichtseenheid',
        ],
    ],
];
