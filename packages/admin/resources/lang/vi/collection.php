<?php

return [

    'label' => 'Bộ sưu tập',

    'plural_label' => 'Bộ sưu tập',

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Bộ sưu tập con',
            'actions' => [
                'create_child' => [
                    'label' => 'Tạo bộ sưu tập con',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Số lượng con',
                ],
                'name' => [
                    'label' => 'Tên',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Thông tin cơ bản',
        ],
        'products' => [
            'label' => 'Sản phẩm',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm sản phẩm',
                ],
            ],
        ],
    ],

];
