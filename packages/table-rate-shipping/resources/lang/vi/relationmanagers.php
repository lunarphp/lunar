<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Liên kết nhóm khách hàng với phương thức vận chuyển này để xác định tính khả dụng của nó.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Phí vận chuyển',
        'actions' => [
            'create' => [
                'label' => 'Tạo phí vận chuyển',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Tất cả giá đã bao gồm thuế, điều này sẽ được xem xét khi tính toán chi tiêu tối thiểu.',
            'prices_excl_tax' => 'Tất cả giá chưa bao gồm thuế, chi tiêu tối thiểu sẽ dựa trên tổng phụ giỏ hàng.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Phương thức vận chuyển',
            ],
            'price' => [
                'label' => 'Giá',
            ],
            'prices' => [
                'label' => 'Mức giá',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Nhóm khách hàng',
                        'placeholder' => 'Bất kỳ',
                    ],
                    'currency_id' => [
                        'label' => 'Tiền tệ',
                    ],
                    'min_quantity' => [
                        'label' => 'Chi tiêu tối thiểu',
                    ],
                    'price' => [
                        'label' => 'Giá',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Phương thức vận chuyển',
            ],
            'price' => [
                'label' => 'Giá',
            ],
            'price_breaks_count' => [
                'label' => 'Mức giá',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Danh sách loại trừ vận chuyển',
        'form' => [
            'purchasable' => [
                'label' => 'Sản phẩm',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Thêm danh sách loại trừ vận chuyển',
            ],
            'attach' => [
                'label' => 'Thêm danh sách loại trừ',
            ],
            'detach' => [
                'label' => 'Xóa',
            ],
        ],
    ],
];
