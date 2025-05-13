<?php

return [
    'label' => 'Đơn hàng',

    'plural_label' => 'Đơn hàng',

    'breadcrumb' => [
        'manage' => 'Quản lý',
    ],

    'tabs' => [
        'all' => 'Tất cả',
    ],

    'transactions' => [
        'capture' => 'Đã thu tiền',
        'intent' => 'Chờ thanh toán',
        'refund' => 'Đã hoàn tiền',
        'failed' => 'Thất bại',
    ],

    'table' => [
        'status' => [
            'label' => 'Trạng thái',
        ],
        'reference' => [
            'label' => 'Mã tham chiếu',
        ],
        'customer_reference' => [
            'label' => 'Mã khách hàng',
        ],
        'customer' => [
            'label' => 'Khách hàng',
        ],
        'tags' => [
            'label' => 'Thẻ',
        ],
        'postcode' => [
            'label' => 'Mã bưu điện',
        ],
        'email' => [
            'label' => 'Email',
            'copy_message' => 'Đã sao chép địa chỉ email',
        ],
        'phone' => [
            'label' => 'Số điện thoại',
        ],
        'total' => [
            'label' => 'Tổng cộng',
        ],
        'date' => [
            'label' => 'Ngày',
        ],
        'new_customer' => [
            'label' => 'Loại khách hàng',
        ],
        'placed_after' => [
            'label' => 'Đặt sau',
        ],
        'placed_before' => [
            'label' => 'Đặt trước',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'Tên',
            ],
            'last_name' => [
                'label' => 'Họ',
            ],
            'line_one' => [
                'label' => 'Địa chỉ dòng 1',
            ],
            'line_two' => [
                'label' => 'Địa chỉ dòng 2',
            ],
            'line_three' => [
                'label' => 'Địa chỉ dòng 3',
            ],
            'company_name' => [
                'label' => 'Tên công ty',
            ],
            'contact_phone' => [
                'label' => 'Số điện thoại',
            ],
            'contact_email' => [
                'label' => 'Địa chỉ email',
            ],
            'city' => [
                'label' => 'Thành phố',
            ],
            'state' => [
                'label' => 'Tỉnh/Thành phố',
            ],
            'postcode' => [
                'label' => 'Mã bưu điện',
            ],
            'country_id' => [
                'label' => 'Quốc gia',
            ],
        ],

        'reference' => [
            'label' => 'Mã tham chiếu',
        ],
        'status' => [
            'label' => 'Trạng thái',
        ],
        'transaction' => [
            'label' => 'Giao dịch',
        ],
        'amount' => [
            'label' => 'Số tiền',
            'hint' => [
                'less_than_total' => 'Bạn sắp thu một số tiền ít hơn tổng giá trị giao dịch',
            ],
        ],
        'notes' => [
            'label' => 'Ghi chú',
        ],
        'confirm' => [
            'label' => 'Xác nhận',
            'alert' => 'Yêu cầu xác nhận',
            'hint' => [
                'capture' => 'Vui lòng xác nhận bạn muốn thu tiền thanh toán này',
                'refund' => 'Vui lòng xác nhận bạn muốn hoàn tiền số tiền này',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Ghi chú',
            'placeholder' => 'Không có ghi chú cho đơn hàng này',
        ],
        'delivery_instructions' => [
            'label' => 'Hướng dẫn giao hàng',
        ],
        'shipping_total' => [
            'label' => 'Tổng phí vận chuyển',
        ],
        'paid' => [
            'label' => 'Đã thanh toán',
        ],
        'refund' => [
            'label' => 'Hoàn tiền',
        ],
        'unit_price' => [
            'label' => 'Đơn giá',
        ],
        'quantity' => [
            'label' => 'Số lượng',
        ],
        'sub_total' => [
            'label' => 'Tạm tính',
        ],
        'discount_total' => [
            'label' => 'Tổng giảm giá',
        ],
        'total' => [
            'label' => 'Tổng cộng',
        ],
        'current_stock_level' => [
            'message' => 'Số lượng tồn kho hiện tại: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'tại thời điểm đặt hàng: :count',
        ],
        'status' => [
            'label' => 'Trạng thái',
        ],
        'reference' => [
            'label' => 'Mã tham chiếu',
        ],
        'customer_reference' => [
            'label' => 'Mã khách hàng',
        ],
        'channel' => [
            'label' => 'Kênh bán hàng',
        ],
        'date_created' => [
            'label' => 'Ngày tạo',
        ],
        'date_placed' => [
            'label' => 'Ngày đặt hàng',
        ],
        'new_returning' => [
            'label' => 'Mới/Quay lại',
        ],
        'new_customer' => [
            'label' => 'Khách hàng mới',
        ],
        'returning_customer' => [
            'label' => 'Khách hàng quay lại',
        ],
        'shipping_address' => [
            'label' => 'Địa chỉ giao hàng',
        ],
        'billing_address' => [
            'label' => 'Địa chỉ thanh toán',
        ],
        'address_not_set' => [
            'label' => 'Chưa có địa chỉ',
        ],
        'billing_matches_shipping' => [
            'label' => 'Giống địa chỉ giao hàng',
        ],
        'additional_info' => [
            'label' => 'Thông tin bổ sung',
        ],
        'no_additional_info' => [
            'label' => 'Không có thông tin bổ sung',
        ],
        'tags' => [
            'label' => 'Thẻ',
        ],
        'timeline' => [
            'label' => 'Dòng thời gian',
        ],
        'transactions' => [
            'label' => 'Giao dịch',
            'placeholder' => 'Không có giao dịch',
        ],
        'alert' => [
            'requires_capture' => 'Đơn hàng này vẫn cần thu tiền thanh toán',
            'partially_refunded' => 'Đơn hàng này đã được hoàn tiền một phần',
            'refunded' => 'Đơn hàng này đã được hoàn tiền',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Cập nhật trạng thái',
            'notification' => 'Đã cập nhật trạng thái đơn hàng',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'Trạng thái mới',
            ],
            'additional_content' => [
                'label' => 'Nội dung bổ sung',
            ],
            'additional_email_recipient' => [
                'label' => 'Người nhận email bổ sung',
                'placeholder' => 'tùy chọn',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Tải PDF',
            'notification' => 'Đang tải PDF đơn hàng',
        ],
        'edit_address' => [
            'label' => 'Chỉnh sửa',
            'notification' => [
                'error' => 'Lỗi',
                'billing_address' => [
                    'saved' => 'Đã lưu địa chỉ thanh toán',
                ],
                'shipping_address' => [
                    'saved' => 'Đã lưu địa chỉ giao hàng',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Chỉnh sửa',
        ],
        'capture_payment' => [
            'label' => 'Thu tiền thanh toán',
            'notification' => [
                'error' => 'Có lỗi khi thu tiền',
                'success' => 'Thu tiền thành công',
            ],
        ],
        'refund_payment' => [
            'label' => 'Hoàn tiền',
            'notification' => [
                'error' => 'Có lỗi khi hoàn tiền',
                'success' => 'Hoàn tiền thành công',
            ],
        ],
    ],
];
