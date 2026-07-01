<?php

return [
    'public_id' => [
        'label' => 'ID công khai',
    ],

    'tags' => [
        'notification' => [
            'updated' => 'Đã cập nhật thẻ',
        ],
    ],
    'activity-log' => [
        'input' => [
            'placeholder' => 'Thêm bình luận',
        ],
        'action' => [
            'add-comment' => 'Thêm bình luận',
        ],
        'system' => 'Hệ thống',
        'partials' => [
            'orders' => [
                'order_created' => 'Đã tạo đơn hàng',
                'status_change' => 'Đã cập nhật trạng thái',
                'order_closed' => 'Đã đóng đơn hàng',
                'order_reopened' => 'Đã mở lại đơn hàng',
                'order_cancelled' => 'Đã hủy đơn hàng (:reason)',
                'order_cancelled_no_reason' => 'Đã hủy đơn hàng',

                'email_notification' => 'Sent :notification to :email',

                'email_notification_fallback' => 'a notification',
                'fulfilment_state' => 'Đơn giao hàng #:id được đánh dấu là :state',
                'fulfilment_held' => 'Đơn giao hàng #:id đã được tạm giữ (:reason)',
                'fulfilment_held_no_reason' => 'Đơn giao hàng #:id đã được tạm giữ',
                'fulfilment_released' => 'Đơn giao hàng #:id đã được gỡ tạm giữ',

                'capture' => 'Thanh toán :amount bằng thẻ có số cuối :last_four',
                'authorized' => 'Đã xác thực :amount bằng thẻ có số cuối :last_four',
                'refund' => 'Hoàn tiền :amount cho thẻ có số cuối :last_four',
                'address' => 'Đã cập nhật :type',
                'billingAddress' => 'Địa chỉ thanh toán',
                'shippingAddress' => 'Địa chỉ giao hàng',
            ],
            'update' => [
                'updated' => 'Đã cập nhật :model',
            ],
            'create' => [
                'created' => 'Đã tạo :model',
            ],
            'tags' => [
                'updated' => 'Đã cập nhật thẻ',
                'added' => 'Đã thêm',
                'removed' => 'Đã xóa',
            ],
        ],
        'notification' => [
            'comment_added' => 'Đã thêm bình luận',
        ],
    ],
    'forms' => [
        'youtube' => [
            'helperText' => 'Nhập ID của video YouTube. Ví dụ: dQw4w9WgXcQ',
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
                'success' => 'Đã sắp xếp lại bộ sưu tập',
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
            'label' => 'Thêm tùy chọn',
        ],
        'delete-option' => [
            'label' => 'Xóa tùy chọn',
        ],
        'remove-shared-option' => [
            'label' => 'Xóa tùy chọn đã chia sẻ',
        ],
        'add-value' => [
            'label' => 'Thêm giá trị khác',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'values' => [
            'label' => 'Giá trị',
        ],
    ],
];
