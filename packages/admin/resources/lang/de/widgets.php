<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Bestellungen heute',
                    'increase' => ':percentage% Anstieg von :count gestern',
                    'decrease' => ':percentage% Rückgang von :count gestern',
                    'neutral' => 'Keine Veränderung im Vergleich zu gestern',
                ],
                'stat_two' => [
                    'label' => 'Bestellungen der letzten 7 Tage',
                    'increase' => ':percentage% Anstieg von :count im letzten Zeitraum',
                    'decrease' => ':percentage% Rückgang von :count im letzten Zeitraum',
                    'neutral' => 'Keine Veränderung im Vergleich zum letzten Zeitraum',
                ],
                'stat_three' => [
                    'label' => 'Bestellungen der letzten 30 Tage',
                    'increase' => ':percentage% Anstieg von :count im letzten Zeitraum',
                    'decrease' => ':percentage% Rückgang von :count im letzten Zeitraum',
                    'neutral' => 'Keine Veränderung im Vergleich zum letzten Zeitraum',
                ],
                'stat_four' => [
                    'label' => 'Umsatz heute',
                    'increase' => ':percentage% Anstieg von :total gestern',
                    'decrease' => ':percentage% Rückgang von :total gestern',
                    'neutral' => 'Keine Veränderung im Vergleich zu gestern',
                ],
                'stat_five' => [
                    'label' => 'Umsatz der letzten 7 Tage',
                    'increase' => ':percentage% Anstieg von :total im letzten Zeitraum',
                    'decrease' => ':percentage% Rückgang von :total im letzten Zeitraum',
                    'neutral' => 'Keine Veränderung im Vergleich zum letzten Zeitraum',
                ],
                'stat_six' => [
                    'label' => 'Umsatz der letzten 30 Tage',
                    'increase' => ':percentage% Anstieg von :total im letzten Zeitraum',
                    'decrease' => ':percentage% Rückgang von :total im letzten Zeitraum',
                    'neutral' => 'Keine Veränderung im Vergleich zum letzten Zeitraum',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Bestellmengen des letzten Jahres',
                'series_one' => [
                    'label' => 'Dieser Zeitraum',
                ],
                'series_two' => [
                    'label' => 'Vorheriger Zeitraum',
                ],
                'yaxis' => [
                    'label' => 'Umsatz :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Bestell-/Umsatzbericht',
                'series_one' => [
                    'label' => 'Bestellungen',
                ],
                'series_two' => [
                    'label' => 'Umsatz',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Bestellungen',
                    ],
                    'series_two' => [
                        'label' => 'Gesamtwert',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Durchschnittlicher Bestellwert',
            ],
            'new_returning_customers' => [
                'heading' => 'Neue vs. wiederkehrende Kunden',
                'series_one' => [
                    'label' => 'Neue Kunden',
                ],
                'series_two' => [
                    'label' => 'Wiederkehrende Kunden',
                ],
            ],
            'popular_products' => [
                'heading' => 'Bestseller dieses Monats',
                'description' => 'Diese Zahlen basieren auf der Anzahl der Bestellungen, in denen ein Produkt erscheint, nicht auf der bestellten Menge.',
            ],
            'latest_orders' => [
                'heading' => 'Neueste Bestellungen',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Gesamtbestellungen',
            ],
            'avg_spend' => [
                'label' => 'Durchschnittlicher Ausgaben',
            ],
            'total_spend' => [
                'label' => 'Gesamtausgaben',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Variante wechseln',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Werte',
            ],
        ],
    ],
];
