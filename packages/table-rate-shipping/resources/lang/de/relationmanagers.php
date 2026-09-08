<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Verknüpfen Sie Kundengruppen mit dieser Versandart, um deren Verfügbarkeit zu bestimmen.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Versandtarife',
        'actions' => [
            'create' => [
                'label' => 'Versandtarif erstellen',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Alle Preise verstehen sich inklusive Steuern, die bei der Berechnung des Mindestbestellwerts berücksichtigt werden.',
            'prices_excl_tax' => 'Alle Preise verstehen sich exklusive Steuern, der Mindestbestellwert basiert auf der Zwischensumme des Warenkorbs.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Versandart',
            ],
            'price' => [
                'label' => 'Preis',
            ],
            'prices' => [
                'label' => 'Preisstaffelungen',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Kundengruppe',
                        'placeholder' => 'Beliebig',
                    ],
                    'currency_id' => [
                        'label' => 'Währung',
                    ],
                    'min_spend' => [
                        'label' => 'Mindestbestellwert',
                    ],
                    'min_weight' => [
                        'label' => 'Mindestgewicht',
                        'helper_text' => 'Gewicht in :unit eingeben',
                    ],
                    'price' => [
                        'label' => 'Preis',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'Aktiviert',
            ],
            'disabled' => [
                'label' => 'deaktiviert',
            ],
            'shipping_method' => [
                'label' => 'Versandart',
                'disabled' => 'Deaktiviert',
            ],
            'price' => [
                'label' => 'Preis',
            ],
            'price_breaks_count' => [
                'label' => 'Preisstaffelungen',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Versandausschlüsse',
        'form' => [
            'purchasable' => [
                'label' => 'Produkt',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Versandausschlussliste hinzufügen',
            ],
            'attach' => [
                'label' => 'Ausschlussliste hinzufügen',
            ],
            'detach' => [
                'label' => 'Entfernen',
            ],
        ],
    ],
];
