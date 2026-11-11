<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Koppel klantengroepen aan deze verzendmethode om de beschikbaarheid te bepalen.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Verzendtarieven',
        'actions' => [
            'create' => [
                'label' => 'Verzendtarief aanmaken',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Alle prijzen zijn inclusief belasting, waarmee rekening wordt gehouden bij het berekenen van het minimale bestelbedrag.',
            'prices_excl_tax' => 'Alle prijzen zijn exclusief belasting, het minimale bestelbedrag wordt gebaseerd op het subtotaal van de winkelwagen.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Verzendmethode',
            ],
            'price' => [
                'label' => 'Prijs',
            ],
            'prices' => [
                'label' => 'Prijsbreuken',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Klantengroep',
                        'placeholder' => 'Alle',
                    ],
                    'currency_id' => [
                        'label' => 'Valuta',
                    ],
                    'min_spend' => [
                        'label' => 'Min. bestelbedrag',
                    ],
                    'min_weight' => [
                        'label' => 'Min. gewicht',
                        'helper_text' => 'Voer het gewicht in :unit in',
                    ],
                    'price' => [
                        'label' => 'Prijs',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'Ingeschakeld',
            ],
            'disabled' => [
                'label' => 'uitgeschakeld',
            ],
            'shipping_method' => [
                'label' => 'Verzendmethode',
                'disabled' => 'Uitgeschakeld',
            ],
            'price' => [
                'label' => 'Prijs',
            ],
            'price_breaks_count' => [
                'label' => 'Prijsbreuken',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Verzenduitsluitingen',
        'form' => [
            'purchasable' => [
                'label' => 'Product',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Verzenduitsluitingslijst toevoegen',
            ],
            'attach' => [
                'label' => 'Uitsluitingslijst toevoegen',
            ],
            'detach' => [
                'label' => 'Verwijderen',
            ],
        ],
    ],
];
