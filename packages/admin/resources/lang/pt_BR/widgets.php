<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Pedidos hoje',
                    'increase' => 'Aumento de :percentage% em relação a :count ontem',
                    'decrease' => 'Diminuição de :percentage% em relação a :count ontem',
                    'neutral' => 'Sem alteração em comparação com ontem',
                ],
                'stat_two' => [
                    'label' => 'Pedidos nos últimos 7 dias',
                    'increase' => 'Aumento de :percentage% em relação a :count no período anterior',
                    'decrease' => 'Diminuição de :percentage% em relação a :count no período anterior',
                    'neutral' => 'Sem alteração em comparação com o período anterior',
                ],
                'stat_three' => [
                    'label' => 'Pedidos nos últimos 30 dias',
                    'increase' => 'Aumento de :percentage% em relação a :count no período anterior',
                    'decrease' => 'Diminuição de :percentage% em relação a :count no período anterior',
                    'neutral' => 'Sem alteração em comparação com o período anterior',
                ],
                'stat_four' => [
                    'label' => 'Vendas hoje',
                    'increase' => 'Aumento de :percentage% em relação a :total ontem',
                    'decrease' => 'Diminuição de :percentage% em relação a :total ontem',
                    'neutral' => 'Sem alteração em comparação com ontem',
                ],
                'stat_five' => [
                    'label' => 'Vendas nos últimos 7 dias',
                    'increase' => 'Aumento de :percentage% em relação a :total no período anterior',
                    'decrease' => 'Diminuição de :percentage% em relação a :total no período anterior',
                    'neutral' => 'Sem alteração em comparação com o período anterior',
                ],
                'stat_six' => [
                    'label' => 'Vendas nos últimos 30 dias',
                    'increase' => 'Aumento de :percentage% em relação a :total no período anterior',
                    'decrease' => 'Diminuição de :percentage% em relação a :total no período anterior',
                    'neutral' => 'Sem alteração em comparação com o período anterior',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Totais de pedidos nos últimos 12 meses',
                'series_one' => [
                    'label' => 'Este Período',
                ],
                'series_two' => [
                    'label' => 'Período Anterior',
                ],
                'yaxis' => [
                    'label' => 'Faturamento :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Relatório de Pedidos / Vendas',
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
                        'label' => 'Valor Total',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Valor Médio do Pedido',
            ],
            'new_returning_customers' => [
                'heading' => 'Novos vs. Clientes Retornados',
                'series_one' => [
                    'label' => 'Novos Clientes',
                ],
                'series_two' => [
                    'label' => 'Clientes Retornados',
                ],
            ],
            'popular_products' => [
                'heading' => 'Os mais vendidos deste mês',
                'description' => 'Esses números são baseados no número de vezes que um produto aparece em um pedido, não na quantidade pedida.',
            ],
            'latest_orders' => [
                'heading' => 'Últimos pedidos',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Total de pedidos',
            ],
            'avg_spend' => [
                'label' => 'Gasto Médio',
            ],
            'total_spend' => [
                'label' => 'Gasto Total',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Alternar Variante',
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
