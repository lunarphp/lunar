<?php

return [
    'label' => 'Kênh',
    'plural_label' => 'Kênh',
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'url' => [
            'label' => 'Đường dẫn',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'url' => [
            'label' => 'Đường dẫn',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],
    ],

    'actions' => [
        'delete' => [
            'blocked' => 'Không thể xóa kênh mặc định, các kênh có lịch sử đơn hàng và các kênh vẫn đang được khu vực hoặc giỏ hàng sử dụng. Thay vào đó, hãy đặt kênh ở trạng thái không hoạt động.',
        ],
    ],

];
