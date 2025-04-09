<?php

return [

    'label' => 'Nhóm thuộc tính',

    'plural_label' => 'Nhóm thuộc tính',

    'table' => [
        'attributable_type' => [
            'label' => 'Loại',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'position' => [
            'label' => 'Vị trí',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Loại',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'position' => [
            'label' => 'Vị trí',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa nhóm thuộc tính này vì có các thuộc tính đang liên kết.',
            ],
        ],
    ],
];
