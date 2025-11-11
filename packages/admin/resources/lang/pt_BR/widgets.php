<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Pedidos hoje',
                    'increase' => ':percentage% de aumento em relação a :count ontem',
                    'decrease' => ':percentage% de queda em relação a :count ontem',
                    'neutral' => 'Sem mudança em relação a ontem',
                ],
                'stat_two' => [
                    'label' => 'Pedidos nos últimos 7 dias',
                    'increase' => ':percentage% de aumento em relação a :count no período anterior',
                    'decrease' => ':percentage% de queda em relação a :count no período anterior',
                    'neutral' => 'Sem mudança em relação ao período anterior',
                ],
                'stat_three' => [
                    'label' => 'Pedidos nos últimos 30 dias',
                    'increase' => ':percentage% de aumento em relação a :count no período anterior',
                    'decrease' => ':percentage% de queda em relação a :count no período anterior',
                    'neutral' => 'Sem mudança em relação ao período anterior',
                ],
                'stat_four' => [
                    'label' => 'Vendas hoje',
                    'increase' => ':percentage% de aumento em relação a :total ontem',
                    'decrease' => ':percentage% de queda em relação a :total ontem',
                    'neutral' => 'Sem mudança em relação a ontem',
                ],
                'stat_five' => [
                    'label' => 'Vendas nos últimos 7 dias',
                    'increase' => ':percentage% de aumento em relação a :total no período anterior',
                    'decrease' => ':percentage% de queda em relação a :total no período anterior',
                    'neutral' => 'Sem mudança em relação ao período anterior',
                ],
                'stat_six' => [
                    'label' => 'Vendas nos últimos 30 dias',
                    'increase' => ':percentage% de aumento em relação a :total no período anterior',
                    'decrease' => ':percentage% de queda em relação a :total no período anterior',
                    'neutral' => 'Sem mudança em relação ao período anterior',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Totais de pedidos do último ano',
                'series_one' => [
                    'label' => 'Este período',
                ],
                'series_two' => [
                    'label' => 'Período anterior',
                ],
                'yaxis' => [
                    'label' => 'Faturamento :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Relatório de pedidos/vendas',
                'series_one' => [
                    'label' => 'Pedidos',
                ],
                'series_two' => [
                    'label' => 'Receita',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Pedidos',
                    ],
                    'series_two' => [
                        'label' => 'Valor total',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Ticket médio',
            ],
            'new_returning_customers' => [
                'heading' => 'Clientes novos x recorrentes',
                'series_one' => [
                    'label' => 'Clientes novos',
                ],
                'series_two' => [
                    'label' => 'Clientes recorrentes',
                ],
            ],
            'popular_products' => [
                'heading' => 'Mais vendidos (últimos 12 meses)',
                'description' => 'Os números se baseiam no número de vezes que um produto aparece em um pedido, e não na quantidade pedida.',
            ],
            'latest_orders' => [
                'heading' => 'Pedidos mais recentes',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Total de pedidos',
            ],
            'avg_spend' => [
                'label' => 'Gasto médio',
            ],
            'total_spend' => [
                'label' => 'Gasto total',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Trocar variação',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Valores',
            ],
        ],
    ],
];
