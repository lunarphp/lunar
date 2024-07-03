<?php

return [
    'dashboard' => [
        'orders' => [
            'order_stats_overview' => [
                'stat_one' => [
                    'label' => 'Đơn hàng hôm nay',
                    'increase' => 'Tăng :percentage% so với :count ngày hôm qua',
                    'decrease' => 'Giảm :percentage% so với :count ngày hôm qua',
                    'neutral' => 'Không thay đổi so với ngày hôm qua',
                ],
                'stat_two' => [
                    'label' => 'Đơn hàng trong 7 ngày qua',
                    'increase' => 'Tăng :percentage% so với kỳ trước',
                    'decrease' => 'Giảm :percentage% so với kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
                'stat_three' => [
                    'label' => 'Đơn hàng trong 30 ngày qua',
                    'increase' => 'Tăng :percentage% so với kỳ trước',
                    'decrease' => 'Giảm :percentage% so với kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
                'stat_four' => [
                    'label' => 'Doanh số hôm nay',
                    'increase' => 'Tăng :percentage% so với :total ngày hôm qua',
                    'decrease' => 'Giảm :percentage% so với :total ngày hôm qua',
                    'neutral' => 'Không thay đổi so với ngày hôm qua',
                ],
                'stat_five' => [
                    'label' => 'Doanh số trong 7 ngày qua',
                    'increase' => 'Tăng :percentage% so với kỳ trước',
                    'decrease' => 'Giảm :percentage% so với kỳ trước',
                    'neutral' => 'Không thay đổi so với kỳ trước',
                ],
                'stat_six' => [
                    'label' => 'Doanh số trong 30 ngày qua',
                    'increase' => 'Tăng :percentage% so với kỳ trước',
                    'decrease' => 'Giảm :percentage% so với kỳ trước',
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
                'heading' => 'Báo cáo Đơn hàng / Doanh thu',
                'series_one' => [
                    'label' => 'Đơn hàng',
                ],
                'series_two' => [
                    'label' => 'Doanh thu',
                ],
                'yaxis' => [
                    'series_one' => [
                        'label' => '# Đơn hàng',
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
                'heading' => 'Khách hàng mới vs Quay lại',
                'series_one' => [
                    'label' => 'Khách hàng mới',
                ],
                'series_two' => [
                    'label' => 'Khách hàng quay lại',
                ],
            ],
            'popular_products' => [
                'heading' => 'Sản phẩm bán chạy nhất trong tháng này',
                'description' => 'Các con số này dựa trên số lần mỗi sản phẩm xuất hiện trong đơn hàng, không phải số lượng đã đặt hàng.',
            ],
            'latest_orders' => [
                'heading' => 'Đơn hàng mới nhất',
            ],
        ],
    ],
    'customer' => [
        'stats_overview' => [
            'total_orders' => [
                'label' => 'Tổng số đơn hàng',
            ],
            'avg_spend' => [
                'label' => 'Trung bình chi tiêu',
            ],
            'total_spend' => [
                'label' => 'Tổng chi tiêu',
            ],
        ],
    ],
    'variant_switcher' => [
        'label' => 'Chuyển đổi Biến thể',
        'table' => [
            'sku' => [
                'label' => 'SKU',
            ],
            'values' => [
                'label' => 'Giá trị',
            ],
        ],
    ],
];
