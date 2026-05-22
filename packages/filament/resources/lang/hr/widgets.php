<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Narudžbe danas',
                    'increase' => ':percentage% porast u odnosu na :count jučer',
                    'decrease' => ':percentage% pad u odnosu na :count jučer',
                    'neutral' => 'Nema promjene u odnosu na jučer',
                ],
                'stat_two' => [
                    'label' => 'Narudžbe u posljednjih 7 dana',
                    'increase' => ':percentage% porast u odnosu na :count u prethodnom razdoblju',
                    'decrease' => ':percentage% pad u odnosu na :count u prethodnom razdoblju',
                    'neutral' => 'Nema promjene u odnosu na prethodno razdoblje',
                ],
                'stat_three' => [
                    'label' => 'Narudžbe u posljednjih 30 dana',
                    'increase' => ':percentage% porast u odnosu na :count u prethodnom razdoblju',
                    'decrease' => ':percentage% pad u odnosu na :count u prethodnom razdoblju',
                    'neutral' => 'Nema promjene u odnosu na prethodno razdoblje',
                ],
                'stat_four' => [
                    'label' => 'Promet danas',
                    'increase' => ':percentage% porast u odnosu na :total jučer',
                    'decrease' => ':percentage% pad u odnosu na :total jučer',
                    'neutral' => 'Nema promjene u odnosu na jučer',
                ],
                'stat_five' => [
                    'label' => 'Promet u posljednjih 7 dana',
                    'increase' => ':percentage% porast u odnosu na :total u prethodnom razdoblju',
                    'decrease' => ':percentage% pad u odnosu na :total u prethodnom razdoblju',
                    'neutral' => 'Nema promjene u odnosu na prethodno razdoblje',
                ],
                'stat_six' => [
                    'label' => 'Promet u posljednjih 30 dana',
                    'increase' => ':percentage% porast u odnosu na :total u prethodnom razdoblju',
                    'decrease' => ':percentage% pad u odnosu na :total u prethodnom razdoblju',
                    'neutral' => 'Nema promjene u odnosu na prethodno razdoblje',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Količine narudžbi u posljednjoj godini',
                'series_one' => [
                    'label' => 'Ovo razdoblje',
                ],
                'series_two' => [
                    'label' => 'Prethodno razdoblje',
                ],
                'yaxis' => [
                    'label' => 'Promet :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Izvještaj narudžbi/prometa',
                'series_one' => [
                    'label' => 'Narudžbe',
                ],
                'series_two' => [
                    'label' => 'Promet',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Narudžbe',
                    ],
                    'series_two' => [
                        'label' => 'Ukupna vrijednost',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Prosječna vrijednost narudžbe',
            ],
            'new_returning_customers' => [
                'heading' => 'Novi vs. stalni kupci',
                'series_one' => [
                    'label' => 'Novi kupci',
                ],
                'series_two' => [
                    'label' => 'Stalni kupci',
                ],
            ],
            'popular_products' => [
                'heading' => 'Najprodavaniji ovog mjeseca',
                'description' => 'Ovi brojevi temelje se na broju narudžbi u kojima se proizvod pojavljuje, a ne na naručenoj količini.',
            ],
            'latest_orders' => [
                'heading' => 'Najnovije narudžbe',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Ukupno narudžbi',
            ],
            'avg_spend' => [
                'label' => 'Prosječna potrošnja',
            ],
            'total_spend' => [
                'label' => 'Ukupna potrošnja',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Promijeni varijantu',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Vrijednosti',
            ],
        ],
    ],
];
