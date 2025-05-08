<?php

return [

    'label' => 'Nhóm khách hàng',

    'plural_label' => 'Nhóm khách hàng',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
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
        'default' => [
            'label' => 'Mặc định',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa nhóm khách hàng này vì đang có khách hàng liên kết.',
            ],
        ],
    ],
];
