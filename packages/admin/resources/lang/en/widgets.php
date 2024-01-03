<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Orders today',
                    'description' => 'from :count yesterday',
                ],
                'stat_two' => [
                    'label' => 'Orders this week',
                    'description' => 'from :count last week',
                ],
                'stat_three' => [
                    'label' => 'Orders this month',
                    'description' => 'from :count last month',
                ],
                'stat_four' => [
                    'label' => 'Sales today',
                    'description' => 'from :total last month',
                ],
                'stat_five' => [
                    'label' => 'Sales this week',
                    'description' => 'from :total last week',
                ],
                'stat_six' => [
                    'label' => 'Sales this month',
                    'description' => 'from :total last month',
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
        ],
    ],
];
