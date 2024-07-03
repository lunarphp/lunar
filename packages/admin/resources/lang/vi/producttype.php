<?php

return [

    'label' => 'Loại Sản phẩm',

    'plural_label' => 'Các loại Sản phẩm',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'products_count' => [
            'label' => 'Số lượng Sản phẩm',
        ],
        'product_attributes_count' => [
            'label' => 'Thuộc tính Sản phẩm',
        ],
        'variant_attributes_count' => [
            'label' => 'Thuộc tính Biến thể',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Thuộc tính Sản phẩm',
        ],
        'variant_attributes' => [
            'label' => 'Thuộc tính Biến thể',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Không có nhóm thuộc tính nào có sẵn.',
        'no_attributes' => 'Không có thuộc tính nào có sẵn.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Không thể xóa loại Sản phẩm này vì có Sản phẩm liên kết.',
            ],
        ],
    ],

];
