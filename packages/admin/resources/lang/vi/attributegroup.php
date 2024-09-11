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
            'label' => 'Handle',
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
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Vị trí',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Nhóm thuộc tính này không thể xóa được vì có các thuộc tính được liên kết.',
            ],
        ],
    ],
];
