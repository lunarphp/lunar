<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Orders today',
                    'increase' => ':percentage% increase from :count yesterday',
                    'decrease' => ':percentage% decrease from :count yesterday',
                    'neutral' => 'No change compared to yesterday',
                ],
                'stat_two' => [
                    'label' => 'Orders past 7 days',
                    'increase' => ':percentage% increase from :count last period',
                    'decrease' => ':percentage% decrease from :count last period',
                    'neutral' => 'No change compared to last period',
                ],
                'stat_three' => [
                    'label' => 'Orders past 30 days',
                    'increase' => ':percentage% increase from :count last period',
                    'decrease' => ':percentage% decrease from :count last period',
                    'neutral' => 'No change compared to last period',
                ],
                'stat_four' => [
                    'label' => 'Sales today',
                    'increase' => ':percentage% increase from :total yesterday',
                    'decrease' => ':percentage% decrease from :total yesterday',
                    'neutral' => 'No change compared to yesterday',
                ],
                'stat_five' => [
                    'label' => 'Sales past 7 days',
                    'increase' => ':percentage% increase from :total last period',
                    'decrease' => ':percentage% decrease from :total last period',
                    'neutral' => 'No change compared to last period',
                ],
                'stat_six' => [
                    'label' => 'Sales past 30 days',
                    'increase' => ':percentage% increase from :total last period',
                    'decrease' => ':percentage% decrease from :total last period',
                    'neutral' => 'No change compared to last period',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Order totals for the past year',
                'series_one' => [
                    'label' => 'This Period',
                ],
                'series_two' => [
                    'label' => 'Previous Period',
                ],
                'yaxis' => [
                    'label' => 'Turnover :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Orders / Sales Report',
                'series_one' => [
                    'label' => 'Orders',
                ],
                'series_two' => [
                    'label' => 'Revenue',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Orders',
                    ],
                    'series_two' => [
                        'label' => 'Total Value',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Average Order Value',
            ],
            'new_returning_customers' => [
                'heading' => 'New vs Returning Customers',
                'series_one' => [
                    'label' => 'New Customers',
                ],
                'series_two' => [
                    'label' => 'Returning Customers',
                ],
            ],
            'popular_products' => [
                'heading' => 'Best sellers (last 12 months)',
                'description' => 'These figures are based on the number of times a product appears on an order, not the quantity ordered.',
            ],
            'latest_orders' => [
                'heading' => 'Latest orders',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Total orders',
            ],
            'avg_spend' => [
                'label' => 'Avg. Spend',
            ],
            'total_spend' => [
                'label' => 'Total Spend',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Switch Variant',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Values',
            ],
        ],
    ],
];
