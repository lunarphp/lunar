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
                    'label' => 'Orders this week',
                    'increase' => ':percentage% increase from :count last week',
                    'decrease' => ':percentage% decrease from :count last week',
                    'neutral' => 'No change compared to last week',
                ],
                'stat_three' => [
                    'label' => 'Orders this month',
                    'increase' => ':percentage% increase from :count last month',
                    'decrease' => ':percentage% decrease from :count last month',
                    'neutral' => 'No change compared to last month',
                ],
                'stat_four' => [
                    'label' => 'Sales today',
                    'increase' => ':percentage% increase from :total yesterday',
                    'decrease' => ':percentage% decrease from :total yesterday',
                    'neutral' => 'No change compared to yesterday',
                ],
                'stat_five' => [
                    'label' => 'Sales this week',
                    'increase' => ':percentage% increase from :total last week',
                    'decrease' => ':percentage% decrease from :total last week',
                    'neutral' => 'No change compared to last week',
                ],
                'stat_six' => [
                    'label' => 'Sales this month',
                    'increase' => ':percentage% increase from :total last month',
                    'decrease' => ':percentage% decrease from :total last month',
                    'neutral' => 'No change compared to last month',
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
                'heading' => 'This months best sellers',
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
];
