<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Đơn hàng hôm nay',
                    'increase' => 'Tăng :percentage% so với :count hôm qua',
                    'decrease' => 'Giảm :percentage% so với :count hôm qua',
                    'neutral' => 'Không thay đổi so với hôm qua',
                ],
                'stat_two' => [
                    'label' => 'Đơn hàng 7 ngày qua',
                    'increase' => 'Tăng :percentage% so với :count kỳ trước',
                    'decrease' => 'Giảm :percentage% so với :count kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
                'stat_three' => [
                    'label' => 'Đơn hàng 30 ngày qua',
                    'increase' => 'Tăng :percentage% so với :count kỳ trước',
                    'decrease' => 'Giảm :percentage% so với :count kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
                'stat_four' => [
                    'label' => 'Doanh số hôm nay',
                    'increase' => 'Tăng :percentage% so với :total hôm qua',
                    'decrease' => 'Giảm :percentage% so với :total hôm qua',
                    'neutral' => 'Không thay đổi so với hôm qua',
                ],
                'stat_five' => [
                    'label' => 'Doanh số 7 ngày qua',
                    'increase' => 'Tăng :percentage% so với :total kỳ trước',
                    'decrease' => 'Giảm :percentage% so với :total kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
                'stat_six' => [
                    'label' => 'Doanh số 30 ngày qua',
                    'increase' => 'Tăng :percentage% so với :total kỳ trước',
                    'decrease' => 'Giảm :percentage% so với :total kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
            ],
            'order_totals_chart' => [
                'heading' => 'Tổng đơn hàng trong năm qua',
                'series_one' => [
                    'label' => 'Kỳ này',
                ],
                'series_two' => [
                    'label' => 'Kỳ trước',
                ],
                'yaxis' => [
                    'label' => 'Doanh thu :currency',
                ],
            ],
            'order_sales_chart' => [
                'heading' => 'Báo cáo đơn hàng / doanh số',
                'series_one' => [
                    'label' => 'Đơn hàng',
                ],
                'series_two' => [
                    'label' => 'Doanh thu',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => 'Số đơn hàng',
                    ],
                    'series_two' => [
                        'label' => 'Tổng giá trị',
                    ],
                ],
            ],
            'average_order_value' => [
                'heading' => 'Giá trị đơn hàng trung bình',
            ],
            'new_returning_customers' => [
                'heading' => 'Khách hàng mới và quay lại',
                'series_one' => [
                    'label' => 'Khách hàng mới',
                ],
                'series_two' => [
                    'label' => 'Khách hàng quay lại',
                ],
            ],
            'popular_products' => [
                'heading' => 'Sản phẩm bán chạy (12 tháng qua)',
                'description' => 'Số liệu này dựa trên số lần sản phẩm xuất hiện trong đơn hàng, không phải số lượng đã đặt.',
            ],
            'latest_orders' => [
                'heading' => 'Đơn hàng mới nhất',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Tổng đơn hàng',
            ],
            'avg_spend' => [
                'label' => 'Chi tiêu trung bình',
            ],
            'total_spend' => [
                'label' => 'Tổng chi tiêu',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Chuyển đổi biến thể',
        'table' => [
            'sku' => [
                'label' => 'Mã SKU',
            ],
            'values' => [
                'label' => 'Giá trị',
            ],
        ],
    ],
];
