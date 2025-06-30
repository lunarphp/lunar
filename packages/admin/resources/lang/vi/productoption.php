<?php

return [

    'label' => 'Tùy chọn sản phẩm',

    'plural_label' => 'Tùy chọn sản phẩm',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'label' => [
            'label' => 'Nhãn',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'shared' => [
            'label' => 'Dùng chung',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'label' => [
            'label' => 'Nhãn',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Đã lưu biến thể sản phẩm',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Hủy',
                ],
                'save-options' => [
                    'label' => 'Lưu tùy chọn',
                ],
                'add-shared-option' => [
                    'label' => 'Thêm tùy chọn dùng chung',
                    'form' => [
                        'product_option' => [
                            'label' => 'Tùy chọn sản phẩm',
                        ],
                        'no_shared_components' => [
                            'label' => 'Không có tùy chọn dùng chung nào.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Thêm tùy chọn',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Chưa có tùy chọn sản phẩm nào được cấu hình',
                    'description' => 'Thêm một tùy chọn dùng chung hoặc riêng để bắt đầu tạo biến thể.',
                ],
            ],
            'options-table' => [
                'title' => 'Tùy chọn sản phẩm',
                'configure-options' => [
                    'label' => 'Cấu hình tùy chọn',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Tùy chọn',
                    ],
                    'values' => [
                        'label' => 'Giá trị',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Biến thể sản phẩm',
                'actions' => [
                    'create' => [
                        'label' => 'Tạo biến thể',
                    ],
                    'edit' => [
                        'label' => 'Sửa',
                    ],
                    'delete' => [
                        'label' => 'Xóa',
                    ],
                ],
                'empty' => [
                    'heading' => 'Chưa có biến thể nào được cấu hình',
                ],
                'table' => [
                    'new' => [
                        'label' => 'MỚI',
                    ],
                    'option' => [
                        'label' => 'Tùy chọn',
                    ],
                    'sku' => [
                        'label' => 'Mã SKU',
                    ],
                    'price' => [
                        'label' => 'Giá',
                    ],
                    'stock' => [
                        'label' => 'Kho',
                    ],
                ],
            ],
        ],
    ],

];
