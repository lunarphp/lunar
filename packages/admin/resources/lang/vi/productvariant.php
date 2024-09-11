<?php

return [
    'label' => 'Biến thể Sản phẩm',
    'plural_label' => 'Các biến thể Sản phẩm',
    'pages' => [
        'edit' => [
            'title' => 'Thông tin cơ bản',
        ],
        'media' => [
            'title' => 'Media',
            'form' => [
                'no_selection' => [
                    'label' => 'Bạn hiện không có hình ảnh nào được chọn cho biến thể này.',
                ],
                'no_media_available' => [
                    'label' => 'Hiện tại không có phương tiện truyền thông nào có sẵn cho sản phẩm này.',
                ],
                'images' => [
                    'label' => 'Hình ảnh chính',
                    'helper_text' => 'Chọn hình ảnh sản phẩm đại diện cho biến thể này.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Các định danh',
        ],
        'inventory' => [
            'title' => 'Tồn kho',
        ],
        'shipping' => [
            'title' => 'Vận chuyển',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Global Trade Item Number (GTIN)',
        ],
        'mpn' => [
            'label' => 'Manufacturer Part Number (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'label' => 'Trong kho',
        ],
        'backorder' => [
            'label' => 'Đặt hàng trước',
        ],
        'purchasable' => [
            'label' => 'Khả năng mua hàng',
            'options' => [
                'always' => 'Luôn luôn',
                'in_stock' => 'Còn hàng',
                'in_stock_or_on_backorder' => 'Còn hàng hoặc đặt hàng trước',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Số lượng đơn vị',
            'helper_text' => 'Số lượng các mặt hàng cá nhân tạo nên 1 đơn vị.',
        ],
        'min_quantity' => [
            'label' => 'Số lượng tối thiểu',
            'helper_text' => 'Số lượng tối thiểu của biến thể sản phẩm có thể mua trong một lần mua hàng.',
        ],
        'quantity_increment' => [
            'label' => 'Số lượng tăng',
            'helper_text' => 'Biến thể sản phẩm phải được mua hàng theo bội số của số lượng này.',
        ],
        'tax_class_id' => [
            'label' => 'Lớp thuế',
        ],
        'shippable' => [
            'label' => 'Có thể gửi hàng',
        ],
        'length_value' => [
            'label' => 'Chiều dài',
        ],
        'length_unit' => [
            'label' => 'Đơn vị chiều dài',
        ],
        'width_value' => [
            'label' => 'Chiều rộng',
        ],
        'width_unit' => [
            'label' => 'Đơn vị chiều rộng',
        ],
        'height_value' => [
            'label' => 'Chiều cao',
        ],
        'height_unit' => [
            'label' => 'Đơn vị chiều cao',
        ],
        'weight_value' => [
            'label' => 'Khối lượng',
        ],
        'weight_unit' => [
            'label' => 'Đơn vị khối lượng',
        ],
    ],
];
