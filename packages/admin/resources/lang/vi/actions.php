<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Tạo bộ sưu tập gốc',
        ],
        'create_child' => [
            'label' => 'Tạo bộ sưu tập con',
        ],
        'move' => [
            'label' => 'Di Chuyển Bộ Sưu Tập',
        ],
        'delete' => [
            'label' => 'Xóa',
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Cập Nhật Trạng Thái',
            'wizard' => [
                'step_one' => [
                    'label' => 'Trạng thái',
                ],
                'step_two' => [
                    'label' => 'Trình Gửi Thư và Thông Báo',
                    'no_mailers' => 'Không có trình gửi thư nào cho trạng thái này.',
                ],
                'step_three' => [
                    'label' => 'Xem Và Lưu Lại',
                    'no_mailers' => 'Không có trình gửi thư nào được chọn để xem trước.',
                ],
            ],
            'notification' => [
                'label' => 'Đã cập nhật trạng thái đơn hàng',
            ],
            'billing_email' => [
                'label' => 'Email Thanh Toán',
            ],
            'shipping_email' => [
                'label' => 'Email Chuyển Hàng',
            ],
        ],

    ],
];
