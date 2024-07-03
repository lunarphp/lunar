<?php

return [

    'label' => 'Thương hiệu',

    'plural_label' => 'Thương hiệu',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'products_count' => [
            'label' => 'Số Lượng Sản Phẩm',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Thương hiệu này không thể bị xóa vì có những sản phẩm liên quan.',
            ],
        ],
    ],
    'pages' => [
        'products' => [
            'label' => 'Sản Phẩm',
            'actions' => [
                'attach' => [
                    'label' => 'Liên kết một sản phẩm',
                    'form' => [
                        'record_id' => [
                            'label' => 'Sản phẩm',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Sản phẩm đã được liên kết với thương hiệu',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Sản phẩm đã được bỏ liên kết với thương hiệu.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Bộ Sưu Tập',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Chọn một bộ sưu tập',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Liên kết một bộ sưu tập',
                ],
            ],
        ],
    ],

];
