<?php

return [

    'label' => 'Sản phẩm',

    'plural_label' => 'Sản phẩm',

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
                'draft' => 'Nháp',
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
            'label' => 'SKU',
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
            'heading' => 'Cập Nhật Trạng Thái',
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
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Loại sản phẩm',
        ],
        'status' => [
            'label' => 'Trạng thái',
            'options' => [
                'published' => [
                    'label' => 'Đã xuất bản',
                    'description' => 'Sản phẩm này sẽ có sẵn trên tất cả các nhóm và kênh khách hàng được kích hoạt',
                ],
                'draft' => [
                    'label' => 'Nháp',
                    'description' => 'Sản phẩm này sẽ được ẩn trên tất cả các kênh và nhóm khách hàng',
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
            'label' => 'Tình trạng',
        ],
        'media' => [
            'label' => 'Phương tiện truyền thông',
        ],
        'identifiers' => [
            'label' => 'Mã định danh sản phẩm',
        ],
        'inventory' => [
            'label' => 'Kho',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Lớp thuế',
                ],
                'tax_ref' => [
                    'label' => 'Tham chiếu thuế',
                    'helper_text' => 'Tùy chọn, để tích hợp với hệ thống của bên thứ ba.',
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
        ],
        'associations' => [
            'label' => 'Liên kết sản phẩm',
        ],
    ],

];
