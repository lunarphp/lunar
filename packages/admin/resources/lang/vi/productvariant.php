<?php

return [
    'label' => 'Biến thể sản phẩm',
    'plural_label' => 'Biến thể sản phẩm',
    'pages' => [
        'edit' => [
            'title' => 'Thông tin cơ bản',
        ],
        'media' => [
            'title' => 'Hình ảnh',
            'form' => [
                'no_selection' => [
                    'label' => 'Bạn chưa chọn hình ảnh cho biến thể này.',
                ],
                'no_media_available' => [
                    'label' => 'Hiện không có hình ảnh nào cho sản phẩm này.',
                ],
                'images' => [
                    'label' => 'Hình ảnh chính',
                    'helper_text' => 'Chọn hình ảnh sản phẩm đại diện cho biến thể này.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Định danh',
        ],
        'inventory' => [
            'title' => 'Kho hàng',
        ],
        'shipping' => [
            'title' => 'Vận chuyển',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'Mã SKU',
        ],
        'gtin' => [
            'label' => 'Mã số thương mại toàn cầu (GTIN)',
        ],
        'mpn' => [
            'label' => 'Mã số sản phẩm của nhà sản xuất (MPN)',
        ],
        'ean' => [
            'label' => 'Mã UPC/EAN',
        ],
        'stock' => [
            'label' => 'Còn hàng',
        ],
        'backorder' => [
            'label' => 'Đặt trước',
        ],
        'purchasable' => [
            'label' => 'Khả năng mua hàng',
            'options' => [
                'always' => 'Luôn luôn',
                'in_stock' => 'Còn hàng',
                'in_stock_or_on_backorder' => 'Còn hàng hoặc đặt trước',
            ],
        ],
        'unit_quantity' => [
            'label' => 'Số lượng đơn vị',
            'helper_text' => 'Số lượng sản phẩm đơn lẻ tạo thành 1 đơn vị.',
        ],
        'min_quantity' => [
            'label' => 'Số lượng tối thiểu',
            'helper_text' => 'Số lượng tối thiểu của biến thể sản phẩm có thể mua trong một lần.',
        ],
        'quantity_increment' => [
            'label' => 'Bước tăng số lượng',
            'helper_text' => 'Biến thể sản phẩm phải được mua theo bội số của số lượng này.',
        ],
        'tax_class_id' => [
            'label' => 'Loại thuế',
        ],
        'shippable' => [
            'label' => 'Có thể vận chuyển',
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
            'label' => 'Cân nặng',
        ],
        'weight_unit' => [
            'label' => 'Đơn vị cân nặng',
        ],
    ],
];
