<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Commandes aujourd\'hui',
                    'increase' => ':percentage% d\'augmentation par rapport à :count hier',
                    'decrease' => ':percentage% de diminution par rapport à :count hier',
                    'neutral' => 'Aucun changement par rapport à hier',
                ],
                'stat_two' => [
                    'label' => 'Commandes des 7 derniers jours',
                    'increase' => ':percentage% d\'augmentation par rapport à :count sur la période précédente',
                    'decrease' => ':percentage% de diminution par rapport à :count sur la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
                'stat_three' => [
                    'label' => 'Commandes des 30 derniers jours',
                    'increase' => ':percentage% d\'augmentation par rapport à :count sur la période précédente',
                    'decrease' => ':percentage% de diminution par rapport à :count sur la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
                'stat_four' => [
                    'label' => 'Ventes aujourd\'hui',
                    'increase' => ':percentage% d\'augmentation par rapport à :total hier',
                    'decrease' => ':percentage% de diminution par rapport à :total hier',
                    'neutral' => 'Aucun changement par rapport à hier',
                ],
                'stat_five' => [
                    'label' => 'Ventes des 7 derniers jours',
                    'increase' => ':percentage% d\'augmentation par rapport à :total sur la période précédente',
                    'decrease' => ':percentage% de diminution par rapport à :total sur la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
                'stat_six' => [
                    'label' => 'Ventes des 30 derniers jours',
                    'increase' => ':percentage% d\'augmentation par rapport à :total sur la période précédente',
                    'decrease' => ':percentage% de diminution par rapport à :total sur la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Totaux des commandes pour l\'année écoulée',
                'series_one' => [
                    'label' => 'Cette période',
                ],
                'series_two' => [
                    'label' => 'Période précédente',
                ],
                'yaxis' => [
                    'label' => 'Chiffre d\'affaires :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Rapport Commandes / Ventes',
                'series_one' => [
                    'label' => 'Commandes',
                ],
                'series_two' => [
                    'label' => 'Revenu',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Commandes',
                    ],
                    'series_two' => [
                        'label' => 'Valeur totale',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Valeur moyenne des commandes',
            ],
            'new_returning_customers' => [
                'heading' => 'Nouveaux vs Clients récurrents',
                'series_one' => [
                    'label' => 'Nouveaux clients',
                ],
                'series_two' => [
                    'label' => 'Clients récurrents',
                ],
            ],
            'popular_products' => [
                'heading' => 'Meilleures ventes (12 derniers mois)',
                'description' => 'Ces chiffres sont basés sur le nombre de fois qu\'un produit apparaît dans une commande, et non sur la quantité commandée.',
            ],
            'latest_orders' => [
                'heading' => 'Dernières commandes',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Total des commandes',
            ],
            'avg_spend' => [
                'label' => 'Dépense moyenne',
            ],
            'total_spend' => [
                'label' => 'Dépense totale',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Changer de variante',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Valeurs',
            ],
        ],
    ],
];
