<?php

return [
    'label' => 'Thuộc tính',
    'plural_label' => 'Thuộc tính',
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'description' => [
            'label' => 'Mô tả',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'type' => [
            'label' => 'Loại',
        ],
        'group' => [
            'label' => 'Group',
            'ungrouped' => 'Ungrouped',
        ],
    ],
    'form' => [
        'attribute_group' => [
            'label' => 'Group',
            'placeholder' => 'No group',
        ],
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'Loại',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'description' => [
            'label' => 'Mô tả',
            'helper' => 'Dùng để hiển thị văn bản trợ giúp bên dưới mục nhập',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'searchable' => [
            'label' => 'Có thể tìm kiếm',
        ],
        'filterable' => [
            'label' => 'Có thể lọc',
        ],
        'required' => [
            'label' => 'Bắt buộc',
        ],
        'type' => [
            'label' => 'Loại',
        ],
        'validation_rules' => [
            'label' => 'Quy tắc xác thực',
            'helper' => 'Mỗi mục một quy tắc, ví dụ: min:1, max:10',
        ],
    ],

    'actions' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'System attributes cannot be deleted.',
            ],
        ],
    ],
];
