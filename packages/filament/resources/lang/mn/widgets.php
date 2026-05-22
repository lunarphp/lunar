<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Өнөөдрийн захиалгууд',
                    'increase' => 'Өчигдрөөс :percentage% өсөлт :count захиалга',
                    'decrease' => 'Өчигдрөөс :percentage% бууралт :count захиалга',
                    'neutral' => 'Өчигдрөөс өөрчлөлт байхгүй',
                ],
                'stat_two' => [
                    'label' => 'Сүүлийн 7 хоногийн захиалгууд',
                    'increase' => 'Өмнөх хугацаанаас :percentage% өсөлт :count захиалга',
                    'decrease' => 'Өмнөх хугацаанаас :percentage% бууралт :count захиалга',
                    'neutral' => 'Өмнөх хугацаанаас өөрчлөлт байхгүй',
                ],
                'stat_three' => [
                    'label' => 'Сүүлийн 30 хоногийн захиалгууд',
                    'increase' => 'Өмнөх хугацаанаас :percentage% өсөлт :count захиалга',
                    'decrease' => 'Өмнөх хугацаанаас :percentage% бууралт :count захиалга',
                    'neutral' => 'Өмнөх хугацаанаас өөрчлөлт байхгүй',
                ],
                'stat_four' => [
                    'label' => 'Өнөөдрийн борлуулалт',
                    'increase' => 'Өчигдрөөс :percentage% өсөлт :total',
                    'decrease' => 'Өчигдрөөс :percentage% бууралт :total',
                    'neutral' => 'Өчигдрөөс өөрчлөлт байхгүй',
                ],
                'stat_five' => [
                    'label' => 'Сүүлийн 7 хоногийн борлуулалт',
                    'increase' => 'Өмнөх хугацаанаас :percentage% өсөлт :total',
                    'decrease' => 'Өмнөх хугацаанаас :percentage% бууралт :total',
                    'neutral' => 'Өмнөх хугацаанаас өөрчлөлт байхгүй',
                ],
                'stat_six' => [
                    'label' => 'Сүүлийн 30 хоногийн борлуулалт',
                    'increase' => 'Өмнөх хугацаанаас :percentage% өсөлт :total',
                    'decrease' => 'Өмнөх хугацаанаас :percentage% бууралт :total',
                    'neutral' => 'Өмнөх хугацаанаас өөрчлөлт байхгүй',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Сүүлийн жилийн захиалгын нийт дүн',
                'series_one' => [
                    'label' => 'Энэ хугацаа',
                ],
                'series_two' => [
                    'label' => 'Өмнөх хугацаа',
                ],
                'yaxis' => [
                    'label' => 'Орлого: :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Захиалгууд / Борлуулалтын тайлан',
                'series_one' => [
                    'label' => 'Захиалгууд',
                ],
                'series_two' => [
                    'label' => 'Орлого',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Захиалга',
                    ],
                    'series_two' => [
                        'label' => 'Нийт дүн',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Захиалгын дундаж үнэ',
            ],
            'new_returning_customers' => [
                'heading' => 'Шинэ vs Дахин ирсэн харилцагчид',
                'series_one' => [
                    'label' => 'Шинэ харилцагчид',
                ],
                'series_two' => [
                    'label' => 'Дахин ирсэн харилцагчид',
                ],
            ],
            'popular_products' => [
                'heading' => 'Хамгийн их борлогдсон (сүүлийн 12 сар)',
                'description' => 'Энэ тоо бүтээгдэхүүн захиалга дээр хэдэн удаа гарснаас гаргасан, захиалсан тооноос биш.',
            ],
            'latest_orders' => [
                'heading' => 'Сүүлийн захиалгууд',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Нийт захиалга',
            ],
            'avg_spend' => [
                'label' => 'Дундаж зарлага',
            ],
            'total_spend' => [
                'label' => 'Нийт зарлага',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Вариант солих',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Утгууд',
            ],
        ],
    ],
];
