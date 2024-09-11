<?php

return [

    'label' => 'Nhóm Bộ Sưu Tập',

    'plural_label' => 'Nhóm Bộ Sưu Tập',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'collections_count' => [
            'label' => 'Số Lượng Bộ Sưu Tập',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'hông thể xóa nhóm bộ sưu tập này vì có các bộ sưu tập được liên kết.',
            ],
        ],
    ],
];
