<?php

return [

    'label' => 'Nhóm bộ sưu tập',

    'plural_label' => 'Nhóm bộ sưu tập',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'collections_count' => [
            'label' => 'Số bộ sưu tập',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa nhóm bộ sưu tập này vì có các bộ sưu tập đang liên kết.',
            ],
        ],
    ],
];
