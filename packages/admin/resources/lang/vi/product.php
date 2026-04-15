<?php

return [

    'label' => 'Sản phẩm',

    'plural_label' => 'Sản phẩm',

    'tabs' => [
        'all' => 'Tất cả',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Hiện đang ở trạng thái nháp, sản phẩm này bị ẩn trên tất cả các kênh và nhóm khách hàng.',
        ],
        'availability' => [
            'customer_groups' => 'Sản phẩm này hiện không có sẵn cho tất cả các nhóm khách hàng.',
            'channels' => 'Sản phẩm này hiện không có sẵn trên tất cả các kênh.',
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
            'heading' => 'Cập nhật trạng thái',
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
            ],
        ],
        'tags' => [
            'label' => 'Thẻ',
        ],
        'collections' => [
            'label' => 'Bộ sưu tập',
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
