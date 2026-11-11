<?php

return [
    'shipping_discount' => [
        'name' => 'Giá vận chuyển',
        'form' => [
            'methods' => [
                'label' => 'Phương thức vận chuyển',
                'add_label' => 'Thêm quy tắc',
            ],
            'shipping_method_id' => [
                'label' => 'Phương thức vận chuyển',
                'placeholder' => 'Bất kỳ phương thức vận chuyển nào',
            ],
            'type' => [
                'label' => 'Loại giảm giá',
                'options' => [
                    'fixed' => 'Giá cố định',
                    'percentage' => 'Giảm theo phần trăm',
                ],
            ],
            'percentage' => [
                'label' => 'Phần trăm giảm (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Quy tắc',
            'rules_description' => 'Mỗi quy tắc áp dụng cho một phương thức vận chuyển, hoặc cho mọi phương thức khi không chọn phương thức nào. Phương thức cụ thể được ưu tiên hơn quy tắc chung.',
            'add_rule' => 'Thêm quy tắc',
            'remove_rule' => 'Xóa quy tắc',
            'no_rules' => 'Chưa có quy tắc nào. Thêm một quy tắc để thay đổi phí vận chuyển.',
            'method' => 'Phương thức vận chuyển',
            'method_any' => 'Bất kỳ phương thức vận chuyển nào',
            'type' => 'Hiệu lực',
            'type_fixed' => 'Đặt giá vận chuyển',
            'type_percentage' => 'Giảm theo phần trăm',
            'percentage' => 'Phần trăm giảm (%)',
            'prices' => 'Giá vận chuyển trở thành',
            'prices_hint' => 'Khách hàng trả số tiền này cho vận chuyển. Để trống một loại tiền tệ để giữ nguyên giá của nó; nhập 0 để miễn phí vận chuyển.',
        ],
        'summary_free' => 'Miễn phí vận chuyển',
        'summary_percentage' => 'Giảm :percentage% phí vận chuyển',
        'summary_from' => 'Vận chuyển từ :amount',
    ],
];
