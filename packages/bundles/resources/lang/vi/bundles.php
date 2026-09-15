<?php

return [
    'selection' => [
        'malformed' => 'Lựa chọn gói sản phẩm không hợp lệ.',
        'unknown_group' => 'Gói sản phẩm không có nhóm tùy chọn ":id".',
        'unknown_component' => 'Tùy chọn ":id" không có trong ":group".',
        'duplicate_component' => 'Một tùy chọn trong ":group" đã được chọn nhiều lần.',
        'count' => 'Hãy chọn từ :min đến :max tùy chọn trong ":group".',
        'unavailable' => 'Thành phần ":identifier" của gói không có sẵn với số lượng này.',
    ],
    'nesting' => [
        'is_component' => 'Một biến thể thuộc gói khác không thể tự là một gói.',
        'is_bundle' => 'Một gói không thể chứa gói khác.',
        'self' => 'Một gói không thể chứa chính biến thể của nó.',
    ],
    'definition' => [
        'empty' => 'Một gói cần ít nhất một thành phần.',
        'too_many_components' => 'Một gói không thể có nhiều hơn :max thành phần.',
        'unknown_group' => 'Nhóm tùy chọn không thuộc gói này.',
        'group_selections' => 'Giới hạn lựa chọn của ":group" phải thỏa mãn tối thiểu <= tối đa <= số tùy chọn.',
    ],
    'pricing' => [
        'missing_component_price' => 'Thành phần ":identifier" không có giá bằng :currency, vì vậy gói không thể được định giá bằng loại tiền đó.',
    ],
    'console' => [
        'repriced' => 'Đã tính lại giá cho :count gói.',
    ],
];
