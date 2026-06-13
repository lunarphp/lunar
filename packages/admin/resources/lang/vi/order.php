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
        'heading' => 'Fulfilments',
        'unreferenced' => 'Fulfilment #:id',
        'on_hold' => 'On hold',
        'empty' => 'No fulfilments yet.',
        'columns' => [
            'reference' => 'Reference',
            'state' => 'State',
            'items' => 'Items',
            'tracking' => 'Tracking',
            'shipped_at' => 'Shipped at',
            'handed_over' => [
                'shipping' => 'Shipped at',
                'collection' => 'Collected at',
                'digital' => 'Provisioned at',
            ],
            'handed_over_default' => 'Fulfilled at',
        ],
        'actions' => [
            'more' => 'More actions',
            'add_tracking' => [
                'label' => 'Add tracking',
                'modal_heading' => 'Add tracking',
                'notification' => [
                    'success' => 'Tracking added.',
                    'error' => 'Could not add tracking.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'Remove tracking',
                'notification' => [
                    'success' => 'Tracking removed.',
                    'error' => 'Could not remove tracking.',
                ],
            ],
            'create' => [
                'label' => 'Create fulfilment',
                'modal_heading' => 'Create fulfilment',
                'empty' => 'Every line is already fulfilled.',
                'notification' => [
                    'success' => 'Fulfilment created.',
                    'error' => 'Could not create fulfilment.',
                ],
            ],
            'ship' => [
                'label' => 'Mark shipped',
                'modal_heading' => 'Mark fulfilment as shipped',
                'notification' => [
                    'success' => 'Fulfilment marked as shipped.',
                    'error' => 'Could not ship fulfilment.',
                ],
            ],
            'fulfil' => [
                'label' => 'Mark fulfilled',
                'modal_heading' => 'Mark fulfilment as fulfilled',
                'labels' => [
                    'collection' => 'Mark collected',
                ],
                'notification' => [
                    'success' => 'Fulfilment marked as fulfilled.',
                    'error' => 'Could not fulfil fulfilment.',
                ],
            ],
            'cancel' => [
                'label' => 'Cancel fulfilment',
                'modal_heading' => 'Cancel fulfilment',
                'description' => 'This returns the fulfilment to pending so it can be progressed again. Any shipment details are cleared.',
                'notification' => [
                    'success' => 'Fulfilment cancelled.',
                    'error' => 'Could not cancel fulfilment.',
                ],
            ],
            'change_location' => [
                'label' => 'Change location',
                'modal_heading' => 'Change fulfilment location',
                'field' => 'Location',
                'notification' => [
                    'success' => 'Fulfilment location updated.',
                    'error' => 'Could not change the fulfilment location.',
                ],
            ],
            'return' => [
                'label' => 'Return',
                'notification' => [
                    'success' => 'Fulfilment returned.',
                    'error' => 'Could not return fulfilment.',
                ],
            ],
            'update_status' => [
                'label' => 'Update status',
            ],
            'transition' => [
                'modal_heading' => 'Mark fulfilment as :status?',
                'notification' => [
                    'success' => 'Fulfilment status updated.',
                    'error' => 'Could not update the fulfilment status.',
                ],
            ],
            'undo_return' => [
                'label' => 'Undo return',
                'notification' => [
                    'success' => 'Return undone.',
                    'error' => 'Could not undo the return.',
                ],
            ],
            'hold' => [
                'label' => 'Hold fulfilment',
                'modal_heading' => 'Hold fulfilment',
                'reason' => 'Reason',
                'note' => 'Note',
                'notification' => [
                    'success' => 'Fulfilment placed on hold.',
                    'error' => 'Could not hold the fulfilment.',
                ],
            ],
            'release' => [
                'label' => 'Release hold',
                'notification' => [
                    'success' => 'Fulfilment released.',
                    'error' => 'Could not release the fulfilment.',
                ],
            ],
            'split' => [
                'label' => 'Split',
                'confirm' => 'Split fulfilment',
                'cancel' => 'Cancel',
                'empty' => 'Select a quantity to split out.',
                'modal_heading' => 'Split fulfilment',
                'notification' => [
                    'success' => 'Fulfilment split.',
                    'error' => 'Could not split fulfilment.',
                ],
            ],
            'merge' => [
                'label' => 'Merge',
                'confirm' => 'Merge fulfilment',
                'cancel' => 'Cancel',
                'modal_heading' => 'Merge fulfilment',
                'description' => 'Select the items you would like to merge.',
                'target' => 'Merge with',
                'empty' => 'Select items and a destination to merge.',
                'notification' => [
                    'success' => 'Fulfilments merged.',
                    'error' => 'Could not merge fulfilments.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'Quantity',
            'tracking' => 'Tracking',
            'tracking_item' => 'Tracking #:number',
            'unit_price' => 'Unit Price',
            'sub_total' => 'Sub Total',
            'discount_total' => 'Discount Total',
            'total' => 'Total',
            'stock_level' => 'Current Stock Level: :count',
            'of' => 'of :count',
            'outstanding' => 'Outstanding: :count',
            'tracking_number' => 'Tracking number',
            'tracking_url' => 'Tracking URL',
            'carrier' => 'Carrier',
            'carrier_custom' => 'Custom / other',
            'tracking_url_help' => 'Only needed for carriers without an automatic tracking link.',
            'shipping_method' => 'Shipping method',
            'move_quantity' => 'Quantity to move out',
        ],
    ],

    'other_items' => [
        'heading' => 'Other items',
    ],
];
