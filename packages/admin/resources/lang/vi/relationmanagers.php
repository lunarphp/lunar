<?php

return [
    'customer_groups' => [
        'actions' => [
            'attach' => [
                'label' => 'Đính kèm Nhóm Khách hàng',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Tên',
            ],
            'enabled' => [
                'label' => 'Đã kích hoạt',
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
                'label' => 'Có thể mua hàng',
            ],
        ],
        'table' => [
            'description' => 'Liên kết các nhóm khách hàng với sản phẩm để xác định tính sẵn có của nó.',
            'name' => [
                'label' => 'Tên',
            ],
            'enabled' => [
                'label' => 'Đã kích hoạt',
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
                'label' => 'Có thể mua hàng',
            ],
        ],
    ],
    'channels' => [
        'actions' => [
            'attach' => [
                'label' => 'Lên lịch Kênh khác',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Đã kích hoạt',
                'helper_text_false' => 'Kênh này sẽ không được kích hoạt ngay cả khi có ngày bắt đầu.',
            ],
            'starts_at' => [
                'label' => 'Ngày bắt đầu',
                'helper_text' => 'Để trống để có sẵn từ bất kỳ ngày nào.',
            ],
            'ends_at' => [
                'label' => 'Ngày kết thúc',
                'helper_text' => 'Để trống để có sẵn vô thời hạn.',
            ],
        ],
        'table' => [
            'description' => 'Xác định các kênh được kích hoạt và lên lịch sẵn có.',
            'name' => [
                'label' => 'Tên',
            ],
            'enabled' => [
                'label' => 'Đã kích hoạt',
            ],
            'starts_at' => [
                'label' => 'Ngày bắt đầu',
            ],
            'ends_at' => [
                'label' => 'Ngày kết thúc',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'Tạo URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Ngôn ngữ',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
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
                'label' => 'Slug',
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
        'title' => 'Giá cho Nhóm Khách hàng',
        'title_plural' => 'Giá cho Nhóm Khách hàng',
        'table' => [
            'heading' => 'Giá cho Nhóm Khách hàng',
            'description' => 'Liên kết giá cho các nhóm khách hàng để xác định giá sản phẩm.',
            'empty_state' => [
                'label' => 'Không có giá cho nhóm khách hàng nào tồn tại.',
                'description' => 'Tạo giá cho nhóm khách hàng để bắt đầu.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Thêm Giá cho Nhóm Khách hàng',
                    'modal' => [
                        'heading' => 'Tạo Giá cho Nhóm Khách hàng',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Giá',
        'title_plural' => 'Giá',
        'tab_name' => 'Phân loại Giá',
        'table' => [
            'heading' => 'Phân loại Giá',
            'description' => 'Giảm giá khi khách hàng mua số lượng lớn.',
            'empty_state' => [
                'label' => 'Không có phân loại giá nào tồn tại.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Thêm Phân loại Giá',
                ],
            ],
            'price' => [
                'label' => 'Giá',
            ],
            'customer_group' => [
                'label' => 'Nhóm Khách hàng',
                'placeholder' => 'Tất cả các Nhóm Khách hàng',
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
                'helper_text' => 'Giá mua hàng trước khi chiết khấu.',
            ],
            'customer_group_id' => [
                'label' => 'Nhóm Khách hàng',
                'placeholder' => 'Tất cả các Nhóm Khách hàng',
                'helper_text' => 'Chọn nhóm khách hàng để áp dụng giá này.',
            ],
            'min_quantity' => [
                'label' => 'Số lượng tối thiểu',
                'helper_text' => 'Chọn số lượng tối thiểu để có giá này.',
                'validation' => [
                    'unique' => 'Nhóm Khách hàng và Số lượng tối thiểu phải là duy nhất.',
                ],
            ],
            'currency_id' => [
                'label' => 'Tiền tệ',
                'helper_text' => 'Chọn đơn vị tiền tệ cho giá này.',
            ],
            'compare_price' => [
                'label' => 'Giá so sánh',
                'helper_text' => 'Giá gốc hoặc giá đề xuất bán lẻ, để so sánh với giá mua hàng.',
            ],
            'basePrices' => [
                'title' => 'Giá',
                'form' => [
                    'price' => [
                        'label' => 'Giá',
                        'helper_text' => 'Giá mua hàng trước khi chiết khấu.',
                    ],
                    'compare_price' => [
                        'label' => 'Giá so sánh',
                        'helper_text' => 'Giá gốc hoặc giá đề xuất bán lẻ, để so sánh với giá mua hàng.',
                    ],
                ],
                'tooltip' => 'Tự động tạo dựa trên tỷ giá hối đoái tiền tệ.',
            ],
        ],
    ],
];
