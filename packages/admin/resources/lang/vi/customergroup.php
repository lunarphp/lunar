<?php

return [

    'label' => 'Nhóm Khách Hàng',

    'plural_label' => 'Nhóm Khách Hàng',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Handle',
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
            'label' => 'Handle',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa nhóm khách hàng này vì có những khách hàng được liên kết.',
            ],
        ],
    ],
];
