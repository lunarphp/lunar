<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Rendelések ma',
                    'increase' => ':percentage% növekedés a tegnapi (:count) értékhez képest',
                    'decrease' => ':percentage% csökkenés a tegnapi (:count) értékhez képest',
                    'neutral' => 'Nincs változás tegnaphoz képest',
                ],
                'stat_two' => [
                    'label' => 'Rendelések az elmúlt 7 napban',
                    'increase' => ':percentage% növekedés a :count előző időszakhoz képest',
                    'decrease' => ':percentage% csökkenés a :count előző időszakhoz képest',
                    'neutral' => 'Nincs változás az előző időszakhoz képest',
                ],
                'stat_three' => [
                    'label' => 'Rendelések az elmúlt 30 napban',
                    'increase' => ':percentage% növekedés a :count előző időszakhoz képest',
                    'decrease' => ':percentage% csökkenés a :count előző időszakhoz képest',
                    'neutral' => 'Nincs változás az előző időszakhoz képest',
                ],
                'stat_four' => [
                    'label' => 'Rendelések ma',
                    'increase' => ':percentage% növekedés a :total tegnapi értékhez képest',
                    'decrease' => ':percentage% csökkenés a :total tegnapi értékhez képest',
                    'neutral' => 'Nincs változás tegnaphoz képest',
                ],
                'stat_five' => [
                    'label' => 'Rendelések az elmúlt 7 napban',
                    'increase' => ':percentage% növekedés a :total előző időszakhoz képest',
                    'decrease' => ':percentage% csökkenés a :total előző időszakhoz képest',
                    'neutral' => 'Nincs változás az előző időszakhoz képest',
                ],
                'stat_six' => [
                    'label' => 'Rendelések az elmúlt 30 napban',
                    'increase' => ':percentage% növekedés a :total előző időszakhoz képest',
                    'decrease' => ':percentage% csökkenés a :total előző időszakhoz képest',
                    'neutral' => 'Nincs változás az előző időszakhoz képest',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Rendelési összesítők az elmúlt évben',
                'series_one' => [
                    'label' => 'Ez az időszak',
                ],
                'series_two' => [
                    'label' => 'Előző időszak',
                ],
                'yaxis' => [
                    'label' => 'Forgalom :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Rendelések / Értékesítési Jelentés',
                'series_one' => [
                    'label' => 'Rendelések',
                ],
                'series_two' => [
                    'label' => 'Bevétel',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Rendelések',
                    ],
                    'series_two' => [
                        'label' => 'Összérték',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Átlagos rendelési érték',
            ],
            'new_returning_customers' => [
                'heading' => 'Új vs Visszatérő Vásárlók',
                'series_one' => [
                    'label' => 'Új Vásárlók',
                ],
                'series_two' => [
                    'label' => 'Visszatérő Vásárlók',
                ],
            ],
            'popular_products' => [
                'heading' => 'Legnépszerűbb termékek (utolsó 12 hónap)',
                'description' => 'Ezek a számok arra épülnek, hányszor szerepel egy termék egy rendelésben, nem a megrendelt mennyiségre.',
            ],
            'latest_orders' => [
                'heading' => 'Legutóbbi rendelések',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Összes rendelés',
            ],
            'avg_spend' => [
                'label' => 'Átlagos költés',
            ],
            'total_spend' => [
                'label' => 'Összes költés',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Variáns váltása',
        'table' => [
            'sku' => [
                'label' => 'Cikkszám (SKU)',
            ],
            'values' => [
                'label' => 'Értékek',
            ],
        ],
    ],
];
