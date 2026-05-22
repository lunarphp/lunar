<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Поръчки днес',
                    'increase' => ':percentage% увеличение спрямо :count вчера',
                    'decrease' => ':percentage% намаление спрямо :count вчера',
                    'neutral' => 'Без промяна спрямо вчера',
                ],
                'stat_two' => [
                    'label' => 'Поръчки за последните 7 дни',
                    'increase' => ':percentage% увеличение спрямо :count за предходния период',
                    'decrease' => ':percentage% намаление спрямо :count за предходния период',
                    'neutral' => 'Без промяна спрямо предходния период',
                ],
                'stat_three' => [
                    'label' => 'Поръчки за последните 30 дни',
                    'increase' => ':percentage% увеличение спрямо :count за предходния период',
                    'decrease' => ':percentage% намаление спрямо :count за предходния период',
                    'neutral' => 'Без промяна спрямо предходния период',
                ],
                'stat_four' => [
                    'label' => 'Продажби днес',
                    'increase' => ':percentage% увеличение спрямо :total вчера',
                    'decrease' => ':percentage% намаление спрямо :total вчера',
                    'neutral' => 'Без промяна спрямо вчера',
                ],
                'stat_five' => [
                    'label' => 'Продажби за последните 7 дни',
                    'increase' => ':percentage% увеличение спрямо :total за предходния период',
                    'decrease' => ':percentage% намаление спрямо :total за предходния период',
                    'neutral' => 'Без промяна спрямо предходния период',
                ],
                'stat_six' => [
                    'label' => 'Продажби за последните 30 дни',
                    'increase' => ':percentage% увеличение спрямо :total за предходния период',
                    'decrease' => ':percentage% намаление спрямо :total за предходния период',
                    'neutral' => 'Без промяна спрямо предходния период',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Общо поръчки за последната година',
                'series_one' => [
                    'label' => 'Този период',
                ],
                'series_two' => [
                    'label' => 'Предходен период',
                ],
                'yaxis' => [
                    'label' => 'Оборот :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Отчет за поръчки / продажби',
                'series_one' => [
                    'label' => 'Поръчки',
                ],
                'series_two' => [
                    'label' => 'Приходи',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => 'Брой поръчки',
                    ],
                    'series_two' => [
                        'label' => 'Обща стойност',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Средна стойност на поръчка',
            ],
            'new_returning_customers' => [
                'heading' => 'Нови срещу върнали се клиенти',
                'series_one' => [
                    'label' => 'Нови клиенти',
                ],
                'series_two' => [
                    'label' => 'Връщащи се клиенти',
                ],
            ],
            'popular_products' => [
                'heading' => 'Най-продавани (последните 12 месеца)',
                'description' => 'Тези данни се основават на броя поръчки, в които се появява продуктът, а не на поръчаното количество.',
            ],
            'latest_orders' => [
                'heading' => 'Последни поръчки',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Общо поръчки',
            ],
            'avg_spend' => [
                'label' => 'Среден разход',
            ],
            'total_spend' => [
                'label' => 'Общ разход',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Смени варианта',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Стойности',
            ],
        ],
    ],
];
