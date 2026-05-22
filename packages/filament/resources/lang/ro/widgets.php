<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Comenzi astăzi',
                    'increase' => 'Creștere de :percentage% față de :count ieri',
                    'decrease' => 'Scădere de :percentage% față de :count ieri',
                    'neutral' => 'Fără schimbare față de ieri',
                ],
                'stat_two' => [
                    'label' => 'Comenzi în ultimele 7 zile',
                    'increase' => 'Creștere de :percentage% față de :count în perioada anterioară',
                    'decrease' => 'Scădere de :percentage% față de :count în perioada anterioară',
                    'neutral' => 'Fără schimbare față de perioada anterioară',
                ],
                'stat_three' => [
                    'label' => 'Comenzi în ultimele 30 de zile',
                    'increase' => 'Creștere de :percentage% față de :count în perioada anterioară',
                    'decrease' => 'Scădere de :percentage% față de :count în perioada anterioară',
                    'neutral' => 'Fără schimbare față de perioada anterioară',
                ],
                'stat_four' => [
                    'label' => 'Vânzări astăzi',
                    'increase' => 'Creștere de :percentage% față de :total ieri',
                    'decrease' => 'Scădere de :percentage% față de :total ieri',
                    'neutral' => 'Fără schimbare față de ieri',
                ],
                'stat_five' => [
                    'label' => 'Vânzări în ultimele 7 zile',
                    'increase' => 'Creștere de :percentage% față de :total în perioada anterioară',
                    'decrease' => 'Scădere de :percentage% față de :total în perioada anterioară',
                    'neutral' => 'Fără schimbare față de perioada anterioară',
                ],
                'stat_six' => [
                    'label' => 'Vânzări în ultimele 30 de zile',
                    'increase' => 'Creștere de :percentage% față de :total în perioada anterioară',
                    'decrease' => 'Scădere de :percentage% față de :total în perioada anterioară',
                    'neutral' => 'Fără schimbare față de perioada anterioară',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Total comenzi în ultimul an',
                'series_one' => [
                    'label' => 'Această perioadă',
                ],
                'series_two' => [
                    'label' => 'Perioada anterioară',
                ],
                'yaxis' => [
                    'label' => 'Cifra de afaceri (:currency)',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Raport comenzi / vânzări',
                'series_one' => [
                    'label' => 'Comenzi',
                ],
                'series_two' => [
                    'label' => 'Venit',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => 'Nr. comenzi',
                    ],
                    'series_two' => [
                        'label' => 'Valoare totală',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Valoarea medie a comenzii',
            ],
            'new_returning_customers' => [
                'heading' => 'Clienți noi vs. recurenți',
                'series_one' => [
                    'label' => 'Clienți noi',
                ],
                'series_two' => [
                    'label' => 'Clienți recurenți',
                ],
            ],
            'popular_products' => [
                'heading' => 'Cele mai vândute (ultimele 12 luni)',
                'description' => 'Aceste cifre se bazează pe numărul de apariții ale unui produs într-o comandă, nu pe cantitatea comandată.',
            ],
            'latest_orders' => [
                'heading' => 'Cele mai recente comenzi',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Total comenzi',
            ],
            'avg_spend' => [
                'label' => 'Cheltuială medie',
            ],
            'total_spend' => [
                'label' => 'Cheltuială totală',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Schimbă varianta',
        'table' => [
            'sku' => [
                'label' => 'Cod stoc intern (SKU)',
            ],
            'values' => [
                'label' => 'Valori',
            ],
        ],
    ],
];
