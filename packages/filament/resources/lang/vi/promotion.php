<?php

return [

    'label' => 'Chương trình khuyến mãi',

    'plural_label' => 'Chương trình khuyến mãi',

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Mã xử lý',
        ],
        'description' => [
            'label' => 'Mô tả',
        ],
        'starts_at' => [
            'label' => 'Ngày bắt đầu',
        ],
        'ends_at' => [
            'label' => 'Ngày kết thúc',
        ],
    ],

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Mã xử lý',
        ],
        'discounts_count' => [
            'label' => 'Số mã giảm giá',
        ],
        'starts_at' => [
            'label' => 'Ngày bắt đầu',
        ],
        'ends_at' => [
            'label' => 'Ngày kết thúc',
        ],
    ],

    'relationmanagers' => [
        'discounts' => [
            'title' => 'Mã giảm giá',
            'description' => 'Các mã giảm giá thuộc chương trình này.',
            'actions' => [
                'associate' => [
                    'label' => 'Liên kết mã giảm giá',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'handle' => [
                    'label' => 'Mã xử lý',
                ],
                'status' => [
                    'label' => 'Trạng thái',
                ],
                'starts_at' => [
                    'label' => 'Ngày bắt đầu',
                ],
                'ends_at' => [
                    'label' => 'Ngày kết thúc',
                ],
            ],
        ],
    ],

];
