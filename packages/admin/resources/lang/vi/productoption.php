<?php

return [

    'label' => 'Tùy chọn Sản phẩm',

    'plural_label' => 'Tùy chọn Sản phẩm',

    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'label' => [
            'label' => 'Nhãn',
        ],
        'handle' => [
            'label' => 'Xử lý',
        ],
        'shared' => [
            'label' => 'Chia sẻ',
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
            'label' => 'Xử lý',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Lưu Biến thể Sản phẩm thành công',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Hủy',
                ],
                'save-options' => [
                    'label' => 'Lưu Tùy chọn',
                ],
                'add-shared-option' => [
                    'label' => 'Thêm Tùy chọn Chia sẻ',
                    'form' => [
                        'product_option' => [
                            'label' => 'Tùy chọn Sản phẩm',
                        ],
                        'no_shared_components' => [
                            'label' => 'Không có tùy chọn chia sẻ nào có sẵn.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Thêm Tùy chọn',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Không có tùy chọn Sản phẩm nào được cấu hình',
                    'description' => 'Thêm tùy chọn Sản phẩm chia sẻ hoặc hạn chế để bắt đầu tạo ra một số biến thể.',
                ],
            ],
            'options-table' => [
                'title' => 'Tùy chọn Sản phẩm',
                'configure-options' => [
                    'label' => 'Cấu hình Tùy chọn',
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
                'title' => 'Biến thể Sản phẩm',
                'actions' => [
                    'create' => [
                        'label' => 'Tạo Biến thể',
                    ],
                    'edit' => [
                        'label' => 'Chỉnh sửa',
                    ],
                    'delete' => [
                        'label' => 'Xóa',
                    ],
                ],
                'empty' => [
                    'heading' => 'Chưa có Biến thể được cấu hình',
                ],
                'table' => [
                    'new' => [
                        'label' => 'MỚI',
                    ],
                    'option' => [
                        'label' => 'Tùy chọn',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Giá',
                    ],
                    'stock' => [
                        'label' => 'Kho hàng',
                    ],
                ],
            ],
        ],
    ],

];
