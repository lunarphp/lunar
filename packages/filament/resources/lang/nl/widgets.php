<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Bestellingen vandaag',
                    'increase' => ':percentage% toename ten opzichte van :count gisteren',
                    'decrease' => ':percentage% afname ten opzichte van :count gisteren',
                    'neutral' => 'Geen verandering ten opzichte van gisteren',
                ],
                'stat_two' => [
                    'label' => 'Bestellingen afgelopen 7 dagen',
                    'increase' => ':percentage% toename ten opzichte van :count vorige periode',
                    'decrease' => ':percentage% afname ten opzichte van :count vorige periode',
                    'neutral' => 'Geen verandering ten opzichte van vorige periode',
                ],
                'stat_three' => [
                    'label' => 'Bestellingen afgelopen 30 dagen',
                    'increase' => ':percentage% toename ten opzichte van :count vorige periode',
                    'decrease' => ':percentage% afname ten opzichte van :count vorige periode',
                    'neutral' => 'Geen verandering ten opzichte van vorige periode',
                ],
                'stat_four' => [
                    'label' => 'Verkopen vandaag',
                    'increase' => ':percentage% toename ten opzichte van :total gisteren',
                    'decrease' => ':percentage% afname ten opzichte van :total gisteren',
                    'neutral' => 'Geen verandering ten opzichte van gisteren',
                ],
                'stat_five' => [
                    'label' => 'Verkopen afgelopen 7 dagen',
                    'increase' => ':percentage% toename ten opzichte van :total vorige periode',
                    'decrease' => ':percentage% afname ten opzichte van :total vorige periode',
                    'neutral' => 'Geen verandering ten opzichte van vorige periode',
                ],
                'stat_six' => [
                    'label' => 'Verkopen afgelopen 30 dagen',
                    'increase' => ':percentage% toename ten opzichte van :total vorige periode',
                    'decrease' => ':percentage% afname ten opzichte van :total vorige periode',
                    'neutral' => 'Geen verandering ten opzichte van vorige periode',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Bestellingstotalen van het afgelopen jaar',
                'series_one' => [
                    'label' => 'Deze Periode',
                ],
                'series_two' => [
                    'label' => 'Vorige Periode',
                ],
                'yaxis' => [
                    'label' => 'Omzet :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Bestellingen / Verkooprapport',
                'series_one' => [
                    'label' => 'Bestellingen',
                ],
                'series_two' => [
                    'label' => 'Omzet',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Bestellingen',
                    ],
                    'series_two' => [
                        'label' => 'Totale Waarde',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Gemiddelde Bestelwaarde',
            ],
            'new_returning_customers' => [
                'heading' => 'Nieuwe vs Terugkerende Klanten',
                'series_one' => [
                    'label' => 'Nieuwe Klanten',
                ],
                'series_two' => [
                    'label' => 'Terugkerende Klanten',
                ],
            ],
            'popular_products' => [
                'heading' => 'Bestverkochte producten (laatste 12 maanden)',
                'description' => 'Deze cijfers zijn gebaseerd op het aantal keren dat een product in een bestelling voorkomt, niet op de bestelde hoeveelheid.',
            ],
            'latest_orders' => [
                'heading' => 'Laatste bestellingen',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Totaal aantal bestellingen',
            ],
            'avg_spend' => [
                'label' => 'Gem. Uitgave',
            ],
            'total_spend' => [
                'label' => 'Totale Uitgave',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Variant Wisselen',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Waarden',
            ],
        ],
    ],
];
