<?php

return [
    'customer_groups' => [
        'title' => 'Nhóm khách hàng',
        'actions' => [
            'attach' => [
                'label' => 'Gắn nhóm khách hàng',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Tên',
            ],
            'enabled' => [
                'label' => 'Kích hoạt',
            ],
            'starts_at' => [
                'label' => 'Ngày bắt đầu',
            ],
            'ends_at' => [
                'label' => 'Ngày kết thúc',
            ],
            'visible' => [
                'label' => 'Hiển thị',
            ],
            'purchasable' => [
                'label' => 'Có thể mua',
            ],
        ],
        'table' => [
            'description' => 'Liên kết nhóm khách hàng với :type này để xác định tính khả dụng.',
            'name' => [
                'label' => 'Tên',
            ],
            'enabled' => [
                'label' => 'Kích hoạt',
            ],
            'starts_at' => [
                'label' => 'Ngày bắt đầu',
            ],
            'ends_at' => [
                'label' => 'Ngày kết thúc',
            ],
            'visible' => [
                'label' => 'Hiển thị',
            ],
            'purchasable' => [
                'label' => 'Có thể mua',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Kênh',
        'actions' => [
            'attach' => [
                'label' => 'Lên lịch kênh khác',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Kích hoạt',
                'helper_text_false' => 'Kênh này sẽ không được kích hoạt ngay cả khi có ngày bắt đầu.',
            ],
            'starts_at' => [
                'label' => 'Ngày bắt đầu',
                'helper_text' => 'Để trống nếu muốn khả dụng từ bất kỳ ngày nào.',
            ],
            'ends_at' => [
                'label' => 'Ngày kết thúc',
                'helper_text' => 'Để trống nếu muốn khả dụng vô thời hạn.',
            ],
        ],
        'table' => [
            'description' => 'Xác định kênh nào được kích hoạt và lên lịch khả dụng.',
            'name' => [
                'label' => 'Tên',
            ],
            'enabled' => [
                'label' => 'Kích hoạt',
            ],
            'starts_at' => [
                'label' => 'Ngày bắt đầu',
            ],
            'ends_at' => [
                'label' => 'Ngày kết thúc',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Phương tiện',
        'title_plural' => 'Phương tiện',
        'actions' => [
            'create' => [
                'label' => 'Tạo phương tiện',
            ],
            'view' => [
                'label' => 'Xem',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Tên',
            ],
            'media' => [
                'label' => 'Hình ảnh',
            ],
            'primary' => [
                'label' => 'Chính',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Hình ảnh',
            ],
            'file' => [
                'label' => 'Tập tin',
            ],
            'name' => [
                'label' => 'Tên',
            ],
            'primary' => [
                'label' => 'Chính',
            ],
        ],
    ],
    'urls' => [
        'title' => 'Đường dẫn',
        'title_plural' => 'Đường dẫn',
        'actions' => [
            'create' => [
                'label' => 'Tạo đường dẫn',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Ngôn ngữ',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Đường dẫn tĩnh',
            ],
            'default' => [
                'label' => 'Mặc định',
            ],
            'language' => [
                'label' => 'Ngôn ngữ',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Đường dẫn tĩnh',
            ],
            'default' => [
                'label' => 'Mặc định',
            ],
            'language' => [
                'label' => 'Ngôn ngữ',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Giá theo nhóm khách hàng',
        'title_plural' => 'Giá theo nhóm khách hàng',
        'table' => [
            'heading' => 'Giá theo nhóm khách hàng',
            'description' => 'Liên kết giá với nhóm khách hàng để xác định giá sản phẩm.',
            'empty_state' => [
                'label' => 'Chưa có giá theo nhóm khách hàng.',
                'description' => 'Tạo giá theo nhóm khách hàng để bắt đầu.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Thêm giá nhóm khách hàng',
                    'modal' => [
                        'heading' => 'Tạo giá nhóm khách hàng',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Giá',
        'title_plural' => 'Giá',
        'tab_name' => 'Giảm giá theo số lượng',
        'table' => [
            'heading' => 'Giảm giá theo số lượng',
            'description' => 'Giảm giá khi khách hàng mua với số lượng lớn.',
            'empty_state' => [
                'label' => 'Chưa có mức giảm giá.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Thêm mức giảm giá',
                ],
            ],
            'price' => [
                'label' => 'Giá',
            ],
            'customer_group' => [
                'label' => 'Nhóm khách hàng',
                'placeholder' => 'Tất cả nhóm khách hàng',
            ],
            'min_quantity' => [
                'label' => 'Số lượng tối thiểu',
            ],
            'currency' => [
                'label' => 'Tiền tệ',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Giá',
                'helper_text' => 'Giá mua, trước khi giảm giá.',
            ],
            'customer_group_id' => [
                'label' => 'Nhóm khách hàng',
                'placeholder' => 'Tất cả nhóm khách hàng',
                'helper_text' => 'Chọn nhóm khách hàng để áp dụng giá này.',
            ],
            'min_quantity' => [
                'label' => 'Số lượng tối thiểu',
                'helper_text' => 'Chọn số lượng tối thiểu để áp dụng giá này.',
                'validation' => [
                    'unique' => 'Nhóm khách hàng và số lượng tối thiểu phải là duy nhất.',
                ],
            ],
            'currency_id' => [
                'label' => 'Tiền tệ',
                'helper_text' => 'Chọn tiền tệ cho giá này.',
            ],
            'compare_price' => [
                'label' => 'Giá so sánh',
                'helper_text' => 'Giá gốc hoặc giá bán lẻ đề xuất, để so sánh với giá mua.',
            ],
            'basePrices' => [
                'title' => 'Giá',
                'form' => [
                    'price' => [
                        'label' => 'Giá',
                        'helper_text' => 'Giá mua, trước khi giảm giá.',
                    ],
                    'compare_price' => [
                        'label' => 'Giá so sánh',
                        'helper_text' => 'Giá gốc hoặc giá bán lẻ đề xuất, để so sánh với giá mua.',
                    ],
                ],
                'tooltip' => 'Tự động tạo dựa trên tỷ giá hối đoái.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Phần trăm',
            ],
            'tax_class' => [
                'label' => 'Loại thuế',
            ],
        ],
    ],
    'values' => [
        'title' => 'Giá trị',
        'form' => [
            'name' => [
                'label' => 'Tên',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Tên',
            ],
            'position' => [
                'label' => 'Vị trí',
            ],
        ],
    ],
];
