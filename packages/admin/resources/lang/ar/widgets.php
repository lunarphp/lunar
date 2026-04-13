<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'الطلبات اليوم',
                    'increase' => 'زيادة بنسبة :percentage% مقارنة بـ :count أمس',
                    'decrease' => 'انخفاض بنسبة :percentage% مقارنة بـ :count أمس',
                    'neutral' => 'لا يوجد أي تغيير مقارنةً بالأمس',
                ],
                'stat_two' => [
                    'label' => 'الطلبات خلال آخر 7 ايام',
                    'increase' => 'زيادة بنسبة :percentage% مقارنة بـ :count في الفترة السابقة',
                    'decrease' => 'انخفاض بنسبة :percentage% مقارنة بـ :count في الفترة السابقة',
                    'neutral' => 'لا يوجد أي تغيير مقارنة بالفترة السابقة',
                ],
                'stat_three' => [
                    'label' => 'الطلبات خلال آخر 30 يوم',
                    'increase' => 'زيادة بنسبة :percentage% مقارنة بـ :count في الفترة السابقة',
                    'decrease' => 'انخفاض بنسبة :percentage% مقارنة بـ :count في الفترة السابقة',
                    'neutral' => 'لا يوجد أي تغيير مقارنة بالفترة السابقة',
                ],
                'stat_four' => [
                    'label' => 'المبيعات اليوم',
                    'increase' => 'زيادة بنسبة :percentage% عن إجمالي :total أمس',
                    'decrease' => 'انخفاض بنسبة :percentage% عن إجمالي :total أمس',
                    'neutral' => 'لا يوجد أي تغيير مقارنةً بالأمس',
                ],
                'stat_five' => [
                    'label' => 'المبيعات خلال آخر 7 أيام',
                    'increase' => 'زيادة بنسبة :percentage% عن إجمالي :total في الفترة السابقة',
                    'decrease' => 'انخفاض بنسبة :percentage% عن إجمالي :total في الفترة السابقة',
                    'neutral' => 'لا يوجد أي تغيير مقارنة بالفترة السابقة',
                ],
                'stat_six' => [
                    'label' => 'المبيعات خلال آخر 30 أيام',
                    'increase' => 'زيادة بنسبة :percentage% عن إجمالي :total في الفترة السابقة',
                    'decrease' => 'انخفاض بنسبة :percentage% عن إجمالي :total في الفترة السابقة',
                    'neutral' => 'لا يوجد أي تغيير مقارنة بالفترة السابقة',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'إجمالي الطلبات خلال العام الماضي',
                'series_one' => [
                    'label' => 'الفترة الحالية',
                ],
                'series_two' => [
                    'label' => 'الفترة السابقة',
                ],
                'yaxis' => [
                    'label' => 'إجمالي المبيعات :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'تقرير الطلبات / المبيعات',
                'series_one' => [
                    'label' => 'الطلبات',
                ],
                'series_two' => [
                    'label' => 'الإيرادات',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# الطلبات',
                    ],
                    'series_two' => [
                        'label' => 'إجمالي القيمة',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'متوسط قيمة الطلب',
            ],
            'new_returning_customers' => [
                'heading' => 'العملاء الجدد مقابل العائدين',
                'series_one' => [
                    'label' => 'العملاء الجدد',
                ],
                'series_two' => [
                    'label' => 'العملاء العائدون',
                ],
            ],
            'popular_products' => [
                'heading' => 'الأكثر مبيعًا (آخر 12 شهرًا)',
                'description' => 'تعتمد هذه الأرقام على عدد مرات ظهور المنتج في الطلبات، وليس على الكمية المطلوبة.',
            ],
            'latest_orders' => [
                'heading' => 'أحدث الطلبات',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'إجمالي الطلبات',
            ],
            'avg_spend' => [
                'label' => 'متوسط الإنفاق',
            ],
            'total_spend' => [
                'label' => 'إجمالي الإنفاق',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'تبديل المتغير',
        'table' => [
            'sku' => [
                'label' => 'رمز التخزين (SKU)',
            ],
            'values' => [
                'label' => 'قيم الخيارات',
            ],
        ],
    ],
];
