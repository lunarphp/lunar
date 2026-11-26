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
        'continue_selling' => [
            'label' => 'Doorverkopen zonder voorraad',
            'tooltip' => 'Aan: deze variant kan ongeacht de voorraad besteld worden. Uit: klanten kunnen alleen kopen wat beschikbaar is.',
        ],
        'sell_against_incoming' => [
            'label' => 'Verkopen op inkomende voorraad',
            'tooltip' => 'Inkomende voorraad telt mee als beschikbaar, zodat klanten verwachte eenheden kunnen bestellen.',
        ],
        'unit_quantity' => [
            'label' => 'Eenheidsaantal',
            'tooltip' => 'Hoeveel individuele items vormen 1 eenheid.',
        ],
        'min_quantity' => [
            'label' => 'Minimale Hoeveelheid',
            'tooltip' => 'De minimale hoeveelheid van een productvariant die in één aankoop kan worden gekocht.',
        ],
        'quantity_increment' => [
            'label' => 'Hoeveelheidsverhoging',
            'tooltip' => 'De productvariant moet in veelvouden van deze hoeveelheid worden gekocht.',
        ],
        'tax_class_id' => [
            'label' => 'Belastingklasse',
        ],
        'shippable' => [
            'label' => 'Verzendbaar',
        ],
        'fulfilment_methods' => [
            'label' => 'Fulfilmentmethoden',
            'helper_text' => 'Hoe deze variant wordt afgehandeld. Een fysieke methode zoals verzending of afhalen aanvinken maakt hem verzendbaar. Vink niets aan als er niets af te handelen is, zoals bij een directe download.',
            'unavailable' => ':methods is op dit moment niet beschikbaar, dus klanten kunnen het niet kiezen. Vink het uit of maak het weer beschikbaar.',
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
