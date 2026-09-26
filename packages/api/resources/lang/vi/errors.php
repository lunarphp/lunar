<?php

return [
    'invalid_query' => [
        'title' => 'Truy vấn không hợp lệ',
    ],
    'query' => [
        'malformed_parameter' => 'Tham số :parameter không đúng định dạng.',
        'unknown_include' => 'Include không xác định ":value" trên :type. Cho phép: :allowed.',
        'include_too_deep' => 'Include ":value" vượt quá độ sâu tối đa :max.',
        'unknown_type' => 'Loại tài nguyên không xác định ":value". Cho phép: :allowed.',
        'unknown_field' => 'Trường không xác định ":value" trên :type. Cho phép: :allowed.',
        'unknown_filter' => 'Bộ lọc không xác định ":value". Cho phép: :allowed.',
        'unknown_operator' => 'Toán tử không xác định ":value" cho bộ lọc ":filter". Cho phép: :allowed.',
        'unknown_sort' => 'Sắp xếp không xác định ":value". Cho phép: :allowed.',
        'invalid_page_size' => 'page[size] phải là số nguyên từ 1 đến :max.',
        'invalid_page_number' => 'page[number] phải là số nguyên dương.',
        'cursor_unsupported' => 'Tài nguyên :type không hỗ trợ phân trang bằng con trỏ.',
        'cursor_and_number' => 'Không thể kết hợp page[cursor] và page[number].',
        'invalid_cursor' => 'page[cursor] không phải là con trỏ hợp lệ.',
        'unknown_page_key' => 'Khóa phân trang không xác định ":value". Cho phép: number, size, cursor.',
    ],
    'resource_not_found' => [
        'title' => 'Không tìm thấy',
        'detail' => 'Không tồn tại tài nguyên :type có id ":id".',
    ],
    'invalid_header' => [
        'title' => 'Header không hợp lệ',
        'detail' => 'Giá trị ":value" của header :header không được nhận dạng.',
    ],
    'invalid_cart_token' => [
        'title' => 'Mã giỏ hàng không hợp lệ',
        'detail' => 'Mã X-Lunar-Cart không hợp lệ hoặc đã hết hạn.',
    ],
    'cart_not_found' => [
        'title' => 'Không tìm thấy giỏ hàng',
        'detail' => 'Giỏ hàng được tham chiếu bởi X-Lunar-Cart không còn tồn tại.',
    ],
    'customer_not_found' => [
        'title' => 'Không có khách hàng',
        'detail' => 'Người dùng đã xác thực không có hồ sơ khách hàng.',
    ],
    'validation_failed' => [
        'title' => 'Xác thực dữ liệu thất bại',
    ],
    'unauthenticated' => [
        'title' => 'Chưa xác thực',
        'detail' => 'Cần có thông tin xác thực hợp lệ.',
    ],
    'forbidden' => [
        'title' => 'Bị cấm',
        'detail' => 'Bạn không có quyền thực hiện hành động này.',
    ],
    'not_found' => [
        'title' => 'Không tìm thấy',
        'detail' => 'Điểm cuối hoặc tài nguyên được yêu cầu không tồn tại.',
    ],
    'method_not_allowed' => [
        'title' => 'Phương thức không được phép',
        'detail' => 'Điểm cuối này không hỗ trợ phương thức HTTP đó.',
    ],
    'too_many_requests' => [
        'title' => 'Quá nhiều yêu cầu',
        'detail' => 'Đã vượt quá giới hạn yêu cầu. Hãy thử lại sau.',
    ],
    'server_error' => [
        'title' => 'Lỗi máy chủ',
        'detail' => 'Đã xảy ra lỗi. Hãy thử lại sau.',
    ],
];
