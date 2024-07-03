<?php

return [

    'label' => 'Đơn hàng',

    'plural_label' => 'Đơn hàng',

    'breadcrumb' => [
        'manage' => 'Manage',
    ],

    'transactions' => [
        'capture' => 'Captured',
        'intent' => 'Intent',
        'refund' => 'Refunded',
        'failed' => 'Failed',
    ],

    'table' => [
        'status' => [
            'label' => 'Trạng thái',
        ],
        'reference' => [
            'label' => 'Tham chiếu',
        ],
        'customer_reference' => [
            'label' => 'Tham chiếu khách hàng',
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
            'copy_message' => 'Email address copied',
        ],
        'phone' => [
            'label' => 'Điện thoại',
        ],
        'total' => [
            'label' => 'Tổng',
        ],
        'date' => [
            'label' => 'Ngày',
        ],
        'new_customer' => [
            'label' => 'Loại Khách Hàng',
        ],
        'placed_after' => [
            'label' => 'Placed after',
        ],
        'placed_before' => [
            'label' => 'Placed before',
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
                'label' => 'Dòng địa chỉ 1',
            ],
            'line_two' => [
                'label' => 'Dòng địa chỉ 2',
            ],
            'line_three' => [
                'label' => 'Dòng địa chỉ 3',
            ],
            'company_name' => [
                'label' => 'Tên công ty',
            ],
            'contact_phone' => [
                'label' => 'Điện thoại',
            ],
            'contact_email' => [
                'label' => 'Email Address',
            ],
            'city' => [
                'label' => 'Thành phố',
            ],
            'state' => [
                'label' => 'Tỉnh',
            ],
            'postcode' => [
                'label' => 'Mã bưu điện',
            ],
            'country_id' => [
                'label' => 'Quốc gia',
            ],
        ],

        'reference' => [
            'label' => 'Tham chiếu',
        ],
        'status' => [
            'label' => 'Trạng thái',
        ],
        'transaction' => [
            'label' => 'Transaction',
        ],
        'amount' => [
            'label' => 'Amount',

            'hint' => [
                'less_than_total' => "You're about to capture an amount less than the total transaction value",
            ],
        ],

        'notes' => [
            'label' => 'Ghi chú',
        ],
        'confirm' => [
            'label' => 'Xác nhận',

            'alert' => 'Confirmation required',

            'hint' => [
                'capture' => 'Please confirm you want to capture this payment',
                'refund' => 'Please confirm you wish to refund this amount.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Ghi chú',
            'placeholder' => 'No notes on this order',
        ],
        'delivery_instructions' => [
            'label' => 'Delivery Instructions',
        ],
        'shipping_total' => [
            'label' => 'Shipping Total',
        ],
        'paid' => [
            'label' => 'Paid',
        ],
        'refund' => [
            'label' => 'Refund',
        ],
        'unit_price' => [
            'label' => 'Unit Price',
        ],
        'quantity' => [
            'label' => 'Quantity',
        ],
        'sub_total' => [
            'label' => 'Sub Total',
        ],
        'discount_total' => [
            'label' => 'Discount Total',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Current Stock Level: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'at time of ordering: :count',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Reference',
        ],
        'customer_reference' => [
            'label' => 'Customer Reference',
        ],
        'channel' => [
            'label' => 'Channel',
        ],
        'date_created' => [
            'label' => 'Date Created',
        ],
        'date_placed' => [
            'label' => 'Date Placed',
        ],
        'new_returning' => [
            'label' => 'New / Returning',
        ],
        'new_customer' => [
            'label' => 'New Customer',
        ],
        'returning_customer' => [
            'label' => 'Returning Customer',
        ],
        'shipping_address' => [
            'label' => 'Shipping Address',
        ],
        'billing_address' => [
            'label' => 'Billing Address',
        ],
        'address_not_set' => [
            'label' => 'No address set',
        ],
        'billing_matches_shipping' => [
            'label' => 'Same as shipping address',
        ],
        'additional_info' => [
            'label' => 'Additional Information',
        ],
        'no_additional_info' => [
            'label' => 'No Additional Information',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'timeline' => [
            'label' => 'Timeline',
        ],
        'transactions' => [
            'label' => 'Transactions',
            'placeholder' => 'No transactions',
        ],
        'alert' => [
            'requires_capture' => 'This order still requires payment to be captured.',
            'partially_refunded' => 'This order has been partially refunded.',
            'refunded' => 'This order has been refunded.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Update Status',
            'notification' => 'Orders status updated',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'New status',
            ],
            'additional_content' => [
                'label' => 'Additional content',
            ],
            'additional_email_recipient' => [
                'label' => 'Additional email recipient',
                'placeholder' => 'optional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Download PDF',
            'notification' => 'Order PDF downloading',
        ],
        'edit_address' => [
            'label' => 'Edit',

            'notification' => [
                'error' => 'Error',

                'billing_address' => [
                    'saved' => 'Billing address saved',
                ],

                'shipping_address' => [
                    'saved' => 'Shipping address saved',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Edit',
        ],
        'capture_payment' => [
            'label' => 'Capture Payment',

            'notification' => [
                'error' => 'There was a problem with the capture',
                'success' => 'Capture successful',
            ],
        ],
        'refund_payment' => [
            'label' => 'Refund',

            'notification' => [
                'error' => 'There was a problem with the refund',
                'success' => 'Refund successful',
            ],
        ],
    ],

];
