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
        'cutoff' => [
            'label' => 'Thời hạn',
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
