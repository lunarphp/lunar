<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'سفارش‌های امروز',
                    'increase' => ':percentage% افزایش نسبت به :count دیروز',
                    'decrease' => ':percentage% کاهش نسبت به :count دیروز',
                    'neutral' => 'بدون تغییر نسبت به دیروز',
                ],
                'stat_two' => [
                    'label' => 'سفارش‌های ۷ روز گذشته',
                    'increase' => ':percentage% افزایش نسبت به :count دوره قبل',
                    'decrease' => ':percentage% کاهش نسبت به :count دوره قبل',
                    'neutral' => 'بدون تغییر نسبت به دوره قبل',
                ],
                'stat_three' => [
                    'label' => 'سفارش‌های ۳۰ روز گذشته',
                    'increase' => ':percentage% افزایش نسبت به :count دوره قبل',
                    'decrease' => ':percentage% کاهش نسبت به :count دوره قبل',
                    'neutral' => 'بدون تغییر نسبت به دوره قبل',
                ],
                'stat_four' => [
                    'label' => 'فروش امروز',
                    'increase' => ':percentage% افزایش نسبت به :total دیروز',
                    'decrease' => ':percentage% کاهش نسبت به :total دیروز',
                    'neutral' => 'بدون تغییر نسبت به دیروز',
                ],
                'stat_five' => [
                    'label' => 'فروش ۷ روز گذشته',
                    'increase' => ':percentage% افزایش نسبت به :total دوره قبل',
                    'decrease' => ':percentage% کاهش نسبت به :total دوره قبل',
                    'neutral' => 'بدون تغییر نسبت به دوره قبل',
                ],
                'stat_six' => [
                    'label' => 'فروش ۳۰ روز گذشته',
                    'increase' => ':percentage% افزایش نسبت به :total دوره قبل',
                    'decrease' => ':percentage% کاهش نسبت به :total دوره قبل',
                    'neutral' => 'بدون تغییر نسبت به دوره قبل',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'مجموع سفارش‌ها در سال گذشته',
                'series_one' => [
                    'label' => 'این دوره',
                ],
                'series_two' => [
                    'label' => 'دوره قبل',
                ],
                'yaxis' => [
                    'label' => 'گردش مالی :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'گزارش سفارش‌ها / فروش',
                'series_one' => [
                    'label' => 'سفارش‌ها',
                ],
                'series_two' => [
                    'label' => 'درآمد',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => 'تعداد سفارش‌ها',
                    ],
                    'series_two' => [
                        'label' => 'ارزش کل',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'میانگین ارزش سفارش',
            ],
            'new_returning_customers' => [
                'heading' => 'مشتریان جدید در برابر بازگشتی',
                'series_one' => [
                    'label' => 'مشتریان جدید',
                ],
                'series_two' => [
                    'label' => 'مشتریان بازگشتی',
                ],
            ],
            'popular_products' => [
                'heading' => 'پرفروش‌ترین‌ها (۱۲ ماه گذشته)',
                'description' => 'این آمار بر اساس تعداد دفعاتی است که یک محصول در سفارش‌ها دیده می‌شود، نه تعداد واحدهای سفارش‌داده‌شده.',
            ],
            'latest_orders' => [
                'heading' => 'آخرین سفارش‌ها',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'مجموع سفارش‌ها',
            ],
            'avg_spend' => [
                'label' => 'میانگین هزینه',
            ],
            'total_spend' => [
                'label' => 'کل هزینه',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'تغییر واریانت',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'مقادیر',
            ],
        ],
    ],
];
