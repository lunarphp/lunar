<?php

return [

    'label' => 'Thương hiệu',

    'plural_label' => 'Thương hiệu',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'products_count' => [
            'label' => 'Số sản phẩm',
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
                'error_protected' => 'Không thể xóa thương hiệu này vì có sản phẩm đang liên kết.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Thông tin cơ bản',
        ],
        'products' => [
            'label' => 'Sản phẩm',
            'actions' => [
                'attach' => [
                    'label' => 'Liên kết sản phẩm',
                    'form' => [
                        'record_id' => [
                            'label' => 'Sản phẩm',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Đã liên kết sản phẩm với thương hiệu',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Đã hủy liên kết sản phẩm',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Bộ sưu tập',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Chọn bộ sưu tập',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Liên kết bộ sưu tập',
                ],
            ],
        ],
    ],

];
