<?php

return [
    'label_plural' => 'Phương thức vận chuyển',
    'label' => 'Phương thức vận chuyển',
    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'description' => [
            'label' => 'Mô tả',
        ],
        'code' => [
            'label' => 'Mã',
        ],
        'schedule' => [
            'label' => 'Availability Schedule',
            'days' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'from' => [
                'label' => 'From',
            ],
            'to' => [
                'label' => 'Until',
                'validation' => [
                    'after' => 'The until time must be after the from time.',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Tính phí theo',
            'options' => [
                'cart_total' => 'Tổng giỏ hàng',
                'weight' => 'Cân nặng',
            ],
        ],
        'driver' => [
            'label' => 'Loại',
            'options' => [
                'ship-by' => 'Tiêu chuẩn',
                'collection' => 'Nhận tại cửa hàng',
            ],
        ],
        'stock_available' => [
            'label' => 'Tất cả sản phẩm trong giỏ hàng phải có sẵn hàng',
        ],
        'weight_unit' => [
            'label' => 'Weight Unit',
            'placeholder' => 'No weight restriction',
        ],
        'min_weight' => [
            'label' => 'Minimum Weight',
        ],
        'max_weight' => [
            'label' => 'Maximum Weight',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'code' => [
            'label' => 'Mã',
        ],
        'driver' => [
            'label' => 'Loại',
            'options' => [
                'ship-by' => 'Tiêu chuẩn',
                'collection' => 'Nhận tại cửa hàng',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Khả dụng',
            'customer_groups' => 'Phương thức vận chuyển này hiện không khả dụng cho tất cả các nhóm khách hàng.',
        ],
    ],
];
