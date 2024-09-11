<?php

return [
    'plural_label' => 'Giảm Giá',
    'label' => 'Giảm Giá',
    'form' => [
        'conditions' => [
            'heading' => 'Điều kiện',
        ],
        'buy_x_get_y' => [
            'heading' => 'Mua X Nhận Y',
        ],
        'amount_off' => [
            'heading' => 'Số Tiền Giảm',
        ],
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'starts_at' => [
            'label' => 'Ngày Bắt Đầu',
        ],
        'ends_at' => [
            'label' => 'Ngày Kết Thúc',
        ],
        'priority' => [
            'label' => 'Độ ưu tiên',
            'helper_text' => 'Những ưu đãi có mức ưu tiên cao hơn sẽ được áp dụng trước.',
            'options' => [
                'low' => [
                    'label' => 'Thấp',
                ],
                'medium' => [
                    'label' => 'Trung bình',
                ],
                'high' => [
                    'label' => 'Cao',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Dừng các chương trình giảm giá khác áp dụng sau đợt giảm giá này',
        ],
        'coupon' => [
            'label' => 'Phiếu Giảm Giá',
            'helper_text' => 'Nhập phiếu giảm giá cần thiết để áp dụng giảm giá, nếu để trống nó sẽ tự động áp dụng.',
        ],
        'max_uses' => [
            'label' => 'Số lần sử dụng tối đa',
            'helper_text' => 'Để trống để sử dụng không giới hạn.',
        ],
        'max_uses_per_user' => [
            'label' => 'Số lần sử dụng tối đa cho mỗi người dùng',
            'helper_text' => 'Để trống để sử dụng không giới hạn.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Số tiền giỏ hàng tối thiểu',
        ],
        'min_qty' => [
            'label' => 'Số lượng sản phẩm',
            'helper_text' => 'Đặt số lượng sản phẩm đủ điều kiện cần thiết để áp dụng giảm giá.',
        ],
        'reward_qty' => [
            'label' => 'Số mặt hàng miễn phí',
            'helper_text' => 'Mỗi mặt hàng được giảm giá bao nhiêu.',
        ],
        'max_reward_qty' => [
            'label' => 'Số lượng thưởng tối đa',
            'helper_text' => 'Số lượng sản phẩm tối đa có thể được giảm giá, bất kể tiêu chí nào.',
        ],
        'automatic_rewards' => [
            'label' => 'Tự động thêm phần thưởng',
            'helper_text' => 'Bật để thêm sản phẩm thưởng khi không có trong giỏ.',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'status' => [
            'label' => 'Trạng thái',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Họat động',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'Chưa giải quyết',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Hết hạn',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Lên kế hoạch',
            ],
        ],
        'type' => [
            'label' => 'Loại',
        ],
        'starts_at' => [
            'label' => 'Ngày Bắt Đầu',
        ],
        'ends_at' => [
            'label' => 'Ngày Kết Thúc',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Khả dụng',
        ],
        'limitations' => [
            'label' => 'Hạn chế',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Collections',
            'description' => 'Select which collections this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Collection',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'brands' => [
            'title' => 'Brands',
            'description' => 'Select which brands this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Brand',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Products',
            'description' => 'Select which products this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Product',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Product Rewards',
            'description' => 'Select which products will be discounted if they exist in the cart and the above conditions are met.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Product',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Product Conditions',
            'description' => 'Select the products required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Product',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Product Variants',
            'description' => 'Select which product variants this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Product Variant',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Option(s)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
