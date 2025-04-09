<?php

return [

    'label' => 'Loại sản phẩm',

    'plural_label' => 'Loại sản phẩm',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'products_count' => [
            'label' => 'Số lượng sản phẩm',
        ],
        'product_attributes_count' => [
            'label' => 'Thuộc tính sản phẩm',
        ],
        'variant_attributes_count' => [
            'label' => 'Thuộc tính biến thể',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Thuộc tính sản phẩm',
        ],
        'variant_attributes' => [
            'label' => 'Thuộc tính biến thể',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Không có nhóm thuộc tính nào.',
        'no_attributes' => 'Không có thuộc tính nào.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa loại sản phẩm này vì đã có sản phẩm liên kết.',
            ],
        ],
    ],

];
