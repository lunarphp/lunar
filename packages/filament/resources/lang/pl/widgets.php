<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Zamówienia dzisiaj',
                    'increase' => ':percentage% wzrost od :count wczoraj',
                    'decrease' => ':percentage% spadek od :count wczoraj',
                    'neutral' => 'Brak zmian w porównaniu z wczoraj',
                ],
                'stat_two' => [
                    'label' => 'Zamówienia z ostatnich 7 dni',
                    'increase' => ':percentage% wzrost od :count w poprzednim okresie',
                    'decrease' => ':percentage% spadek od :count w poprzednim okresie',
                    'neutral' => 'Brak zmian w porównaniu z poprzednim okresem',
                ],
                'stat_three' => [
                    'label' => 'Zamówienia z ostatnich 30 dni',
                    'increase' => ':percentage% wzrost od :count w poprzednim okresie',
                    'decrease' => ':percentage% spadek od :count w poprzednim okresie',
                    'neutral' => 'Brak zmian w porównaniu z poprzednim okresem',
                ],
                'stat_four' => [
                    'label' => 'Sprzedaż dzisiaj',
                    'increase' => ':percentage% wzrost od :total wczoraj',
                    'decrease' => ':percentage% spadek od :total wczoraj',
                    'neutral' => 'Brak zmian w porównaniu z wczoraj',
                ],
                'stat_five' => [
                    'label' => 'Sprzedaż z ostatnich 7 dni',
                    'increase' => ':percentage% wzrost od :total w poprzednim okresie',
                    'decrease' => ':percentage% spadek od :total w poprzednim okresie',
                    'neutral' => 'Brak zmian w porównaniu z poprzednim okresem',
                ],
                'stat_six' => [
                    'label' => 'Sprzedaż z ostatnich 30 dni',
                    'increase' => ':percentage% wzrost od :total w poprzednim okresie',
                    'decrease' => ':percentage% spadek od :total w poprzednim okresie',
                    'neutral' => 'Brak zmian w porównaniu z poprzednim okresem',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Suma z zamówień z ostatniego roku',
                'series_one' => [
                    'label' => 'Aktualny okres',
                ],
                'series_two' => [
                    'label' => 'Poprzedni okres',
                ],
                'yaxis' => [
                    'label' => 'Obrót :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Raport zamówień / sprzedaży',
                'series_one' => [
                    'label' => 'Zamówienia',
                ],
                'series_two' => [
                    'label' => 'Sprzedaż',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Zamówień',
                    ],
                    'series_two' => [
                        'label' => 'Wartość zamówień',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Średnia wartość zamówienia',
            ],
            'new_returning_customers' => [
                'heading' => 'Nowi i powracający klienci',
                'series_one' => [
                    'label' => 'Nowi klienci',
                ],
                'series_two' => [
                    'label' => 'Powracający klienci',
                ],
            ],
            'popular_products' => [
                'heading' => 'Popularne produkty w tym miesiącu',
                'description' => 'Wartości te są oparte na liczbie razy, kiedy produkt pojawia się w zamówieniu, a nie na ilości zamówionej.',
            ],
            'latest_orders' => [
                'heading' => 'Ostatnie zamówienia',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Wszystkie zamówienia',
            ],
            'avg_spend' => [
                'label' => 'Średni wydatek',
            ],
            'total_spend' => [
                'label' => 'Wszystkie wydatki',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Zmień wariant',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Wartości',
            ],
        ],
    ],
];
