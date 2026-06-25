<?php

return [
    'label' => 'Đơn hàng',
    'plural_label' => 'Đơn hàng',
    'breadcrumb' => [
        'manage' => 'Quản lý',
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
            'tax_identifier' => [
                'label' => 'Tax Identifier',
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
            'label' => 'Order',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
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

    'fulfilments' => [
        'heading' => 'Đơn giao hàng',
        'unreferenced' => 'Đơn giao hàng #:id',
        'on_hold' => 'Đang tạm giữ',
        'empty' => 'Chưa có đơn giao hàng nào.',
        'columns' => [
            'reference' => 'Mã tham chiếu',
            'state' => 'Trạng thái',
            'items' => 'Mặt hàng',
            'tracking' => 'Theo dõi vận chuyển',
            'shipped_at' => 'Thời điểm giao',
            'handed_over' => [
                'shipping' => 'Thời điểm giao',
                'collection' => 'Thời điểm nhận',
                'digital' => 'Thời điểm cung cấp',
            ],
            'handed_over_default' => 'Thời điểm hoàn thành',
        ],
        'actions' => [
            'more' => 'Thao tác khác',
            'notify' => 'Thông báo cho khách hàng',
            'add_tracking' => [
                'label' => 'Thêm theo dõi vận chuyển',
                'modal_heading' => 'Thêm theo dõi vận chuyển',
                'notification' => [
                    'success' => 'Đã thêm theo dõi vận chuyển.',
                    'error' => 'Không thể thêm theo dõi vận chuyển.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Xóa theo dõi vận chuyển',
                'notification' => [
                    'success' => 'Đã xóa theo dõi vận chuyển.',
                    'error' => 'Không thể xóa theo dõi vận chuyển.',
                ],
            ],
            'create' => [
                'label' => 'Tạo đơn giao hàng',
                'modal_heading' => 'Tạo đơn giao hàng',
                'empty' => 'Mọi dòng đều đã được hoàn thành giao hàng.',
                'notification' => [
                    'success' => 'Đã tạo đơn giao hàng.',
                    'error' => 'Không thể tạo đơn giao hàng.',
                ],
            ],
            'ship' => [
                'label' => 'Đánh dấu đã giao',
                'modal_heading' => 'Đánh dấu đơn giao hàng là đã giao',
                'notification' => [
                    'success' => 'Đã đánh dấu đơn giao hàng là đã giao.',
                    'error' => 'Không thể giao đơn giao hàng.',
                ],
            ],
            'fulfil' => [
                'label' => 'Đánh dấu đã hoàn thành',
                'modal_heading' => 'Đánh dấu đơn giao hàng là đã hoàn thành',
                'labels' => [
                    'collection' => 'Đánh dấu đã nhận',
                ],
                'notification' => [
                    'success' => 'Đã đánh dấu đơn giao hàng là đã hoàn thành.',
                    'error' => 'Không thể hoàn thành đơn giao hàng.',
                ],
            ],
            'cancel' => [
                'label' => 'Hủy đơn giao hàng',
                'modal_heading' => 'Hủy đơn giao hàng',
                'description' => 'Thao tác này đưa đơn giao hàng về trạng thái chờ xử lý để có thể tiếp tục xử lý lại. Mọi thông tin vận chuyển sẽ bị xóa.',
                'notification' => [
                    'success' => 'Đã hủy đơn giao hàng.',
                    'error' => 'Không thể hủy đơn giao hàng.',
                ],
            ],
            'change_location' => [
                'label' => 'Đổi địa điểm',
                'modal_heading' => 'Đổi địa điểm đơn giao hàng',
                'field' => 'Địa điểm',
                'notification' => [
                    'success' => 'Đã cập nhật địa điểm đơn giao hàng.',
                    'error' => 'Không thể đổi địa điểm đơn giao hàng.',
                ],
            ],
            'return' => [
                'label' => 'Trả hàng',
                'notification' => [
                    'success' => 'Đã trả hàng đơn giao hàng.',
                    'error' => 'Không thể trả hàng đơn giao hàng.',
                ],
            ],
            'update_status' => [
                'label' => 'Cập nhật trạng thái',
            ],
            'transition' => [
                'modal_heading' => 'Đánh dấu đơn giao hàng là :status?',
                'notification' => [
                    'success' => 'Đã cập nhật trạng thái đơn giao hàng.',
                    'error' => 'Không thể cập nhật trạng thái đơn giao hàng.',
                ],
            ],
            'undo_return' => [
                'label' => 'Hoàn tác trả hàng',
                'notification' => [
                    'success' => 'Đã hoàn tác trả hàng.',
                    'error' => 'Không thể hoàn tác trả hàng.',
                ],
            ],
            'hold' => [
                'label' => 'Tạm giữ đơn giao hàng',
                'modal_heading' => 'Tạm giữ đơn giao hàng',
                'reason' => 'Lý do',
                'note' => 'Ghi chú',
                'notification' => [
                    'success' => 'Đã tạm giữ đơn giao hàng.',
                    'error' => 'Không thể tạm giữ đơn giao hàng.',
                ],
            ],
            'release' => [
                'label' => 'Gỡ tạm giữ',
                'notification' => [
                    'success' => 'Đã gỡ tạm giữ đơn giao hàng.',
                    'error' => 'Không thể gỡ tạm giữ đơn giao hàng.',
                ],
            ],
            'split' => [
                'label' => 'Tách',
                'confirm' => 'Tách đơn giao hàng',
                'cancel' => 'Hủy',
                'empty' => 'Chọn số lượng cần tách ra.',
                'modal_heading' => 'Tách đơn giao hàng',
                'notification' => [
                    'success' => 'Đã tách đơn giao hàng.',
                    'error' => 'Không thể tách đơn giao hàng.',
                ],
            ],
            'merge' => [
                'label' => 'Gộp',
                'confirm' => 'Gộp đơn giao hàng',
                'cancel' => 'Hủy',
                'modal_heading' => 'Gộp đơn giao hàng',
                'description' => 'Chọn các mặt hàng bạn muốn gộp.',
                'target' => 'Gộp với',
                'empty' => 'Chọn các mặt hàng và một đích đến để gộp.',
                'notification' => [
                    'success' => 'Đã gộp các đơn giao hàng.',
                    'error' => 'Không thể gộp các đơn giao hàng.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Số lượng',
            'tracking' => 'Theo dõi vận chuyển',
            'tracking_item' => 'Mã theo dõi #:number',
            'unit_price' => 'Đơn giá',
            'sub_total' => 'Tạm tính',
            'discount_total' => 'Tổng giảm giá',
            'total' => 'Tổng cộng',
            'stock_level' => 'Mức tồn kho hiện tại: :count',
            'of' => 'trên :count',
            'outstanding' => 'Còn lại: :count',
            'tracking_number' => 'Mã theo dõi',
            'tracking_url' => 'URL theo dõi',
            'carrier' => 'Đơn vị vận chuyển',
            'carrier_custom' => 'Tùy chỉnh / khác',
            'tracking_url_help' => 'Chỉ cần thiết với các đơn vị vận chuyển không có liên kết theo dõi tự động.',
            'shipping_method' => 'Phương thức vận chuyển',
            'move_quantity' => 'Số lượng cần chuyển ra',
        ],
    ],

    'other_items' => [
        'heading' => 'Mặt hàng khác',
    ],
];
