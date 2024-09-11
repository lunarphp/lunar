<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Các thể đã được cập nhật',
        ],
    ],

    'activity-log' => [

        'input' => [

            'placeholder' => 'Thêm một bình luận',

        ],

        'action' => [

            'add-comment' => 'Thêm bình luận',

        ],

        'system' => 'Hệ thống',

        'partials' => [
            'orders' => [
                'order_created' => 'Đơn Hàng Đã Được Tạo',

                'status_change' => 'Trạng thái đã được cập nhật',

                'capture' => 'Thanh toán :amount vào thẻ có 4 số cuối :last_four',

                'authorized' => 'Authorized of :amount on card ending :last_four',

                'refund' => 'Hoàn lại :amount vào thẻ có 4 số cuối :last_four',

                'address' => ':type đã được cập nhật',

                'billingAddress' => 'Địa chỉ thanh toán',

                'shippingAddress' => 'Địa chỉ giao hàng',
            ],

            'update' => [
                'updated' => ':model đã được cập nhật',
            ],

            'create' => [
                'created' => ':model đã được tạo',
            ],

            'tags' => [
                'updated' => 'Các thẻ đã được cập nhật',
                'added' => 'Đã thêm',
                'removed' => 'Đã xóa',
            ],
        ],

        'notification' => [
            'comment_added' => 'Bình luận đã được thêm',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Nhập ID của video YouTube. ví dụ. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Bộ sưu tập cha',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Sắp Xếp Lại Các Bộ Sưu Tập',
            ],
            'node-expanded' => [
                'danger' => 'Không thể tải bộ sưu tập',
            ],
            'delete' => [
                'danger' => 'Không thể xóa bộ sưu tập',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Thêm Tùy Chọn',
        ],
        'delete-option' => [
            'label' => 'Xóa Tùy Chọn',
        ],
        'remove-shared-option' => [
            'label' => 'Xóa tùy chọn chia sẻ',
        ],
        'add-value' => [
            'label' => 'Thêm một giá trị khác',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'values' => [
            'label' => 'Giá trị',
        ],
    ],
];
