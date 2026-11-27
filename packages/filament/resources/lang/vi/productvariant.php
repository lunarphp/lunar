<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
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
        'continue_selling' => [
            'label' => 'Tiếp tục bán khi hết hàng',
            'tooltip' => 'Khi bật, biến thể này có thể được đặt bất kể tồn kho. Khi tắt, khách hàng chỉ có thể mua số lượng còn sẵn.',
        ],
        'sell_against_incoming' => [
            'label' => 'Bán theo hàng sắp về',
            'tooltip' => 'Tính hàng sắp về là còn sẵn, để khách hàng có thể đặt những đơn vị bạn đang chờ nhận.',
        ],
        'unit_quantity' => [
            'label' => 'Số lượng đơn vị',
            'tooltip' => 'Số lượng sản phẩm đơn lẻ tạo thành 1 đơn vị.',
        ],
        'min_quantity' => [
            'label' => 'Số lượng tối thiểu',
            'tooltip' => 'Số lượng tối thiểu của biến thể sản phẩm có thể mua trong một lần.',
        ],
        'quantity_increment' => [
            'label' => 'Bước tăng số lượng',
            'tooltip' => 'Biến thể sản phẩm phải được mua theo bội số của số lượng này.',
        ],
        'tax_class_id' => [
            'label' => 'Loại thuế',
        ],
        'shippable' => [
            'label' => 'Có thể vận chuyển',
        ],
        'fulfilment_methods' => [
            'label' => 'Phương thức thực hiện đơn',
            'helper_text' => 'Cách biến thể này được thực hiện. Chọn một phương thức vật lý như giao hàng hoặc nhận tại cửa hàng sẽ khiến nó có thể giao. Không chọn gì khi không có gì cần thực hiện, ví dụ tải xuống ngay.',
            'unavailable' => ':methods hiện không khả dụng nên khách hàng không thể chọn. Bỏ chọn hoặc làm cho phương thức khả dụng trở lại.',
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
