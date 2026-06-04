<?php

return [
    'label' => 'Sản phẩm',
    'plural_label' => 'Sản phẩm',
    'tabs' => [
        'all' => 'Tất cả',
        'published' => 'Published',
        'draft' => 'Draft',
        'archived' => 'Archived',
    ],
    'status' => [
        'availability' => [
            'customer_groups' => 'Sản phẩm này hiện không có sẵn cho tất cả các nhóm khách hàng.',
            'channels' => 'Sản phẩm này hiện không có sẵn trên tất cả các kênh.',
            'hidden_from_guests' => 'Khách hiện không thể xem hoặc mua sản phẩm này. Nhóm khách hàng mặc định không được bật hoặc hiển thị cho sản phẩm này.',
            'no_default_customer_group' => 'Chưa thiết lập nhóm khách hàng mặc định, do đó không thể kiểm soát khả năng hiển thị cho khách tại đây. Đánh dấu một nhóm khách hàng làm mặc định để quản lý quyền truy cập của khách.',
        ],
    ],
    'table' => [
        'status' => [
            'label' => 'Trạng thái',
            'states' => [
                'deleted' => 'Đã xóa',
                'draft' => 'Bản nháp',
                'published' => 'Đã xuất bản',
            ],
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'brand' => [
            'label' => 'Thương hiệu',
        ],
        'sku' => [
            'label' => 'Mã sản phẩm',
        ],
        'stock' => [
            'label' => 'Kho hàng',
        ],
        'producttype' => [
            'label' => 'Loại sản phẩm',
        ],
    ],
    'actions' => [
        'edit_status' => [
            'label' => 'Cập nhật trạng thái',
            'label_with_state' => 'Status: :state',
            'heading' => 'Cập nhật trạng thái',
        ],
        'delete' => [
            'confirm' => 'This permanently deletes the product and cannot be undone. If you want to hide it from the storefront without losing it, archive it instead.',
            'blocked' => 'This product has appeared on past orders and cannot be deleted — archive it instead so historical orders keep their reference.',
            'disabled_tooltip' => 'Products with order history can\'t be deleted. Archive instead.',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'brand' => [
            'label' => 'Thương hiệu',
        ],
        'sku' => [
            'label' => 'Mã sản phẩm',
        ],
        'producttype' => [
            'label' => 'Loại sản phẩm',
        ],
        'status' => [
            'label' => 'Trạng thái',
            'options' => [
                'published' => [
                    'label' => 'Đã xuất bản',
                    'description' => 'Sản phẩm này sẽ có sẵn trên tất cả các nhóm khách hàng và kênh đã kích hoạt',
                ],
                'draft' => [
                    'label' => 'Bản nháp',
                    'description' => 'Sản phẩm này sẽ bị ẩn trên tất cả các kênh và nhóm khách hàng',
                ],
                'archived' => [
                    'label' => 'Archived',
                    'description' => 'This product is retired but still referenced by historical orders. Move back to Draft to revive it.',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Thẻ',
            'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
        ],
        'collections' => [
            'label' => 'Bộ sưu tập',
            'select_collection' => 'Select a collection',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Tình trạng có sẵn',
        ],
        'edit' => [
            'title' => 'Thông tin cơ bản',
        ],
        'identifiers' => [
            'label' => 'Định danh sản phẩm',
        ],
        'inventory' => [
            'label' => 'Kho hàng',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Loại thuế',
                ],
                'tax_ref' => [
                    'label' => 'Mã tham chiếu thuế',
                    'helper_text' => 'Tùy chọn, dùng để tích hợp với hệ thống bên thứ ba.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Vận chuyển',
        ],
        'variants' => [
            'label' => 'Biến thể',
        ],
        'collections' => [
            'label' => 'Bộ sưu tập',
            'select_collection' => 'Chọn bộ sưu tập',
        ],
        'associations' => [
            'label' => 'Liên kết sản phẩm',
        ],
    ],
];
