<?php

return [
    'dashboard' => [
        'commands' => [
            'label' => 'Commandes',
        ],
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Commandes de jour',
                    'increase' => ':percentage% d\'augmentation par rapport à :count hier',
                    'decrease' => ':percentage% de décrément par rapport à :count hier',
                    'neutral' => 'Aucun changement par rapport à hier',
                ],
                'stat_two' => [
                    'label' => 'Commandes du dernier semaine',
                    'increase' => ':percentage% d\'augmentation par rapport à :count de la période précédente',
                    'decrease' => ':percentage% de décrément par rapport à :count de la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
                'stat_three' => [
                    'label' => 'Commandes du dernier mois',
                    'increase' => ':percentage% d\'augmentation par rapport à :count de la période précédente',
                    'decrease' => ':percentage% de décrément par rapport à :count de la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
                'stat_four' => [
                    'label' => 'Ventes de jour',
                    'increase' => ':percentage% d\'augmentation par rapport à :total hier',
                    'decrease' => ':percentage% de décrément par rapport à :total hier',
                    'neutral' => 'Aucun changement par rapport à hier',
                ],
                'stat_five' => [
                    'label' => 'Ventes du dernier semaine',
                    'increase' => ':percentage% d\'augmentation par rapport à :total de la période précédente',
                    'decrease' => ':percentage% de décrément par rapport à :total de la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
                'stat_six' => [
                    'label' => 'Ventes du dernier mois',
                    'increase' => ':percentage% d\'augmentation par rapport à :total de la période précédente',
                    'decrease' => ':percentage% de décrément par rapport à :total de la période précédente',
                    'neutral' => 'Aucun changement par rapport à la période précédente',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Résumé des chiffres d\'affaires des commandes',
                'series_one' => [
                    'label' => 'Cette période',
                ],
                'series_two' => [
                    'label' => 'Période précédente',
                ],
                'yaxis' => [
                    'label' => 'Chiffre d\'affaires :devise',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Rapport commandes / ventes',
                'series_one' => [
                    'label' => 'Commandes',
                ],
                'series_two' => [
                    'label' => 'Ventes',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Commandes',
                    ],
                    'series_two' => [
                        'label' => 'Total',
                    ],
                ],
            ],
        ],
        'customer' => [
            'label' => 'Client',
        ],
    ],
    'customer' => [
        'commands' => [
            'label' => 'Clients',
        ],
        'orders' => [
            'label' => 'Commandes',
            'table' => [
                'id' => [
                    'label' => 'ID',
                ],
                'created_at' => [
                    'label' => 'Date de création',
                ],
                'total' => [
                    'label' => 'Total',
                ],
            ],
        ],
        'customer_details' => [
            'label' => 'Détails du client',
            'table' => [
                'id' => [
                    'label' => 'ID',
                ],
                'email' => [
                    'label' => 'E-mail',
                ],
                'name' => [
                    'label' => 'Nom',
                ],
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Changer la variante',
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
