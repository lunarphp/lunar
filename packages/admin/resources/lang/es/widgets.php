<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Pedidos hoy',
                    'increase' => 'Aumento del :percentage% desde :count ayer',
                    'decrease' => 'Disminución del :percentage% desde :count ayer',
                    'neutral' => 'Sin cambios en comparación con ayer',
                ],
                'stat_two' => [
                    'label' => 'Pedidos en los últimos 7 días',
                    'increase' => 'Aumento del :percentage% desde :count el periodo anterior',
                    'decrease' => 'Disminución del :percentage% desde :count el periodo anterior',
                    'neutral' => 'Sin cambios en comparación con el periodo anterior',
                ],
                'stat_three' => [
                    'label' => 'Pedidos en los últimos 30 días',
                    'increase' => 'Aumento del :percentage% desde :count el periodo anterior',
                    'decrease' => 'Disminución del :percentage% desde :count el periodo anterior',
                    'neutral' => 'Sin cambios en comparación con el periodo anterior',
                ],
                'stat_four' => [
                    'label' => 'Ventas hoy',
                    'increase' => 'Aumento del :percentage% desde :total ayer',
                    'decrease' => 'Disminución del :percentage% desde :total ayer',
                    'neutral' => 'Sin cambios en comparación con ayer',
                ],
                'stat_five' => [
                    'label' => 'Ventas en los últimos 7 días',
                    'increase' => 'Aumento del :percentage% desde :total el periodo anterior',
                    'decrease' => 'Disminución del :percentage% desde :total el periodo anterior',
                    'neutral' => 'Sin cambios en comparación con el periodo anterior',
                ],
                'stat_six' => [
                    'label' => 'Ventas en los últimos 30 días',
                    'increase' => 'Aumento del :percentage% desde :total el periodo anterior',
                    'decrease' => 'Disminución del :percentage% desde :total el periodo anterior',
                    'neutral' => 'Sin cambios en comparación con el periodo anterior',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Totales de pedidos del último año',
                'series_one' => [
                    'label' => 'Este Periodo',
                ],
                'series_two' => [
                    'label' => 'Periodo Anterior',
                ],
                'yaxis' => [
                    'label' => 'Ingresos :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Informe de Pedidos / Ventas',
                'series_one' => [
                    'label' => 'Pedidos',
                ],
                'series_two' => [
                    'label' => 'Ingresos',
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
                'heading' => 'Valor Promedio del Pedido',
            ],
            'new_returning_customers' => [
                'heading' => 'Nuevos vs Clientes Recurrentes',
                'series_one' => [
                    'label' => 'Nuevos Clientes',
                ],
                'series_two' => [
                    'label' => 'Clientes Recurrentes',
                ],
            ],
            'popular_products' => [
                'heading' => 'Más vendidos (últimos 12 meses)',
                'description' => 'Estas cifras se basan en el número de veces que un producto aparece en un pedido, no en la cantidad pedida.',
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
                'label' => 'Gasto Promedio',
            ],
            'total_spend' => [
                'label' => 'Gasto Total',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Cambiar Variante',
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
