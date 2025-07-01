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
            'label' => 'Di chuyển bộ sưu tập',
        ],
        'delete' => [
            'label' => 'Xóa',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Không thể xóa',
                    'body' => 'Bộ sưu tập này có chứa các bộ sưu tập con và không thể xóa được.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Cập nhật trạng thái',
            'wizard' => [
                'step_one' => [
                    'label' => 'Trạng thái',
                ],
                'step_two' => [
                    'label' => 'Thư thông báo & Thông báo',
                    'no_mailers' => 'Không có thư thông báo nào cho trạng thái này.',
                ],
                'step_three' => [
                    'label' => 'Xem trước & Lưu',
                    'no_mailers' => 'Không có thư thông báo nào được chọn để xem trước.',
                ],
            ],
            'notification' => [
                'label' => 'Đã cập nhật trạng thái đơn hàng',
            ],
            'billing_email' => [
                'label' => 'Email thanh toán',
            ],
            'shipping_email' => [
                'label' => 'Email vận chuyển',
            ],
        ],
    ],
];
