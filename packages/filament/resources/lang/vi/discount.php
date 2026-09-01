<?php

return [
    'plural_label' => 'Mã giảm giá',
    'label' => 'Mã giảm giá',
    'form' => [
        'conditions' => [
            'heading' => 'Điều kiện',
        ],
        'buy_x_get_y' => [
            'heading' => 'Mua X tặng Y',
        ],
        'percentage_off' => [
            'heading' => 'Giảm giá theo phần trăm',
        ],
        'fixed_amount_off' => [
            'heading' => 'Giảm giá số tiền cố định',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Mã xử lý',
        ],
        'starts_at' => [
            'label' => 'Ngày bắt đầu',
        ],
        'ends_at' => [
            'label' => 'Ngày kết thúc',
        ],
        'priority' => [
            'label' => 'Độ ưu tiên',
            'helper_text' => 'Giảm giá có độ ưu tiên cao hơn sẽ được áp dụng trước.',
        ],
        'stop' => [
            'label' => 'Dừng áp dụng các giảm giá khác sau mã này',
            'helper_text' => 'When this discount applies, any discount with a lower priority will be skipped. Give discounts different priorities to control the order they apply in.',
        ],
        'coupon' => [
            'label' => 'Mã giảm giá',
            'helper_text' => 'Nhập mã giảm giá cần thiết để áp dụng, nếu để trống sẽ tự động áp dụng.',
        ],
        'max_uses' => [
            'label' => 'Số lần sử dụng tối đa',
            'helper_text' => 'Để trống nếu không giới hạn số lần sử dụng.',
        ],
        'max_uses_per_user' => [
            'label' => 'Số lần sử dụng tối đa mỗi người dùng',
            'helper_text' => 'Để trống nếu không giới hạn số lần sử dụng.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Số tiền giỏ hàng tối thiểu',
        ],
        'min_qty' => [
            'label' => 'Số lượng sản phẩm',
            'helper_text' => 'Đặt số lượng sản phẩm đủ điều kiện cần thiết để áp dụng giảm giá.',
        ],
        'reward_qty' => [
            'label' => 'Số lượng sản phẩm miễn phí',
            'helper_text' => 'Số lượng mỗi sản phẩm được giảm giá.',
        ],
        'max_reward_qty' => [
            'label' => 'Số lượng quà tặng tối đa',
            'helper_text' => 'Số lượng sản phẩm tối đa có thể được giảm giá, bất kể tiêu chí.',
        ],
        'automatic_rewards' => [
            'label' => 'Tự động thêm quà tặng',
            'helper_text' => 'Bật để tự động thêm sản phẩm quà tặng khi không có trong giỏ hàng.',
        ],
        'percentage' => [
            'label' => 'Phần trăm',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'status' => [
            'label' => 'Trạng thái',
            'active' => [
                'label' => 'Đang hoạt động',
            ],
            'pending' => [
                'label' => 'Đang chờ',
            ],
            'expired' => [
                'label' => 'Đã hết hạn',
            ],
            'scheduled' => [
                'label' => 'Đã lên lịch',
            ],
        ],
        'type' => [
            'label' => 'Loại',
        ],
        'starts_at' => [
            'label' => 'Ngày bắt đầu',
        ],
        'ends_at' => [
            'label' => 'Ngày kết thúc',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Khả dụng',
        ],
        'edit' => [
            'title' => 'Thông tin cơ bản',
        ],
        'limitations' => [
            'label' => 'Giới hạn',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Bộ sưu tập',
            'description' => 'Chọn những bộ sưu tập mà giảm giá này sẽ được giới hạn.',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm bộ sưu tập',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'type' => [
                    'label' => 'Loại',
                    'limitation' => [
                        'label' => 'Giới hạn',
                    ],
                    'exclusion' => [
                        'label' => 'Loại trừ',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Giới hạn',
                        ],
                        'exclusion' => [
                            'label' => 'Loại trừ',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Customers',
            'description' => 'Select which customers this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Customer',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Thương hiệu',
            'description' => 'Chọn những thương hiệu mà giảm giá này sẽ được giới hạn.',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm thương hiệu',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'type' => [
                    'label' => 'Loại',
                    'limitation' => [
                        'label' => 'Giới hạn',
                    ],
                    'exclusion' => [
                        'label' => 'Loại trừ',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Giới hạn',
                        ],
                        'exclusion' => [
                            'label' => 'Loại trừ',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Sản phẩm',
            'description' => 'Chọn những sản phẩm mà giảm giá này sẽ được giới hạn.',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm sản phẩm',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'type' => [
                    'label' => 'Loại',
                    'limitation' => [
                        'label' => 'Giới hạn',
                    ],
                    'exclusion' => [
                        'label' => 'Loại trừ',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Giới hạn',
                        ],
                        'exclusion' => [
                            'label' => 'Loại trừ',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Sản phẩm quà tặng',
            'description' => 'Chọn những sản phẩm sẽ được giảm giá nếu chúng tồn tại trong giỏ hàng và đáp ứng các điều kiện trên.',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm sản phẩm',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'type' => [
                    'label' => 'Loại',
                    'limitation' => [
                        'label' => 'Giới hạn',
                    ],
                    'exclusion' => [
                        'label' => 'Loại trừ',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Giới hạn',
                        ],
                        'exclusion' => [
                            'label' => 'Loại trừ',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Điều kiện sản phẩm',
            'description' => 'Chọn các sản phẩm cần thiết để áp dụng giảm giá.',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm sản phẩm',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'type' => [
                    'label' => 'Loại',
                    'limitation' => [
                        'label' => 'Giới hạn',
                    ],
                    'exclusion' => [
                        'label' => 'Loại trừ',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Giới hạn',
                        ],
                        'exclusion' => [
                            'label' => 'Loại trừ',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'Collection Conditions',
            'description' => 'Select the collection conditions required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Condition',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Biến thể sản phẩm',
            'description' => 'Chọn những biến thể sản phẩm mà giảm giá này sẽ được giới hạn.',
            'actions' => [
                'attach' => [
                    'label' => 'Thêm biến thể sản phẩm',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Tên',
                ],
                'sku' => [
                    'label' => 'Mã SKU',
                ],
                'values' => [
                    'label' => 'Tùy chọn',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Giới hạn',
                        ],
                        'exclusion' => [
                            'label' => 'Loại trừ',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
