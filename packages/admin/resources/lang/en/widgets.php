<?php

return [
    'dashboard' => [
        'stats_overview' => [
            'stat_one' => [
                'label' => 'New Products',
            ],
            'stat_two' => [
                'label' => 'Returning Customers',
            ],
            'stat_three' => [
                'label' => 'Turnover',
            ],
            'stat_four' => [
                'label' => 'No. Orders',
            ],
        ],
        'sales_performance' => [
            'heading' => 'Sales Performance',
            'chart' => [
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
        ],
        'latest_orders' => [
            'heading' => 'Latest Orders',
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label'=> 'Total orders'
            ],
            'avg_spend' => [
                'label'=> 'Avg. Spend'
            ],
            'total_spend' => [
                'label'=> 'Total Spend'
            ],
        ]
    ]
];
