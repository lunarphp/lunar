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
                    'label' => 'Tạo Bộ Sưu Tập Con',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Số Lượng Bộ Sưu Tập Con',
                ],
                'name' => [
                    'label' => 'Tên',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Thông Tin Cơ Bản',
        ],
        'media' => [
            'label' => 'Đa phương tiện',
        ],
        'products' => [
            'label' => 'Sản phẩm',
            'actions' => [
                'attach' => [
                    'label' => 'Liên kết sản phẩm',
                ],
            ],
        ],
    ],

];
