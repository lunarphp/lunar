<?php

return [
    'label' => 'Order',
    'plural_label' => 'Orders',
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
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Reference',
        ],
        'customer_reference' => [
            'label' => 'Customer Reference',
        ],
        'customer' => [
            'label' => 'Customer',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'postcode' => [
            'label' => 'Postcode',
        ],
        'email' => [
            'label' => 'Email',
            'copy_message' => 'Email address copied',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Date',
        ],
        'new_customer' => [
            'label' => 'Customer Type',
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
                'label' => 'First Name',
            ],
            'last_name' => [
                'label' => 'Last Name',
            ],
            'line_one' => [
                'label' => 'Address Line 1',
            ],
            'line_two' => [
                'label' => 'Address Line 2',
            ],
            'line_three' => [
                'label' => 'Address Line 3',
            ],
            'company_name' => [
                'label' => 'Company Name',
            ],
            'tax_identifier' => [
                'label' => 'Tax Identifier',
            ],
            'contact_phone' => [
                'label' => 'Phone',
            ],
            'contact_email' => [
                'label' => 'Email Address',
            ],
            'city' => [
                'label' => 'City',
            ],
            'state' => [
                'label' => 'State / Province',
            ],
            'postcode' => [
                'label' => 'Postal Code',
            ],
            'country_id' => [
                'label' => 'Country',
            ],
        ],
        'reference' => [
            'label' => 'Reference',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'Transaction',
        ],
        'amount' => [
            'label' => 'Amount',
            'hint' => [
                'less_than_total' => 'You\'re about to capture an amount less than the total transaction value',
            ],
        ],
        'notes' => [
            'label' => 'Notes',
        ],
        'confirm' => [
            'label' => 'Confirm',
            'alert' => 'Confirmation required',
            'hint' => [
                'capture' => 'Please confirm you want to capture this payment',
                'refund' => 'Please confirm you wish to refund this amount.',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'Notes',
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
            'label' => 'Order',
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
            'label' => 'Update Status',
            'notification' => 'Order status updated',
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
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
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

    'fulfilments' => [
        'heading' => 'تأمین‌های سفارش',
        'unreferenced' => 'تأمین سفارش #:id',
        'on_hold' => 'در حالت تعلیق',
        'empty' => 'هنوز تأمین سفارشی وجود ندارد.',
        'columns' => [
            'reference' => 'شماره مرجع',
            'state' => 'وضعیت',
            'items' => 'اقلام',
            'tracking' => 'رهگیری',
            'shipped_at' => 'زمان ارسال',
            'handed_over' => [
                'shipping' => 'زمان ارسال',
                'collection' => 'زمان تحویل حضوری',
                'digital' => 'زمان تأمین دیجیتال',
            ],
            'handed_over_default' => 'زمان تأمین',
        ],
        'actions' => [
            'more' => 'اقدامات بیشتر',
            'notify' => 'اطلاع‌رسانی به مشتری',
            'add_tracking' => [
                'label' => 'افزودن رهگیری',
                'modal_heading' => 'افزودن رهگیری',
                'notification' => [
                    'success' => 'رهگیری افزوده شد.',
                    'error' => 'افزودن رهگیری ممکن نشد.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'حذف رهگیری',
                'notification' => [
                    'success' => 'رهگیری حذف شد.',
                    'error' => 'حذف رهگیری ممکن نشد.',
                ],
            ],
            'create' => [
                'label' => 'ایجاد تأمین سفارش',
                'modal_heading' => 'ایجاد تأمین سفارش',
                'empty' => 'همه ردیف‌ها از پیش تأمین شده‌اند.',
                'notification' => [
                    'success' => 'تأمین سفارش ایجاد شد.',
                    'error' => 'ایجاد تأمین سفارش ممکن نشد.',
                ],
            ],
            'ship' => [
                'label' => 'علامت‌گذاری به‌عنوان ارسال‌شده',
                'modal_heading' => 'علامت‌گذاری تأمین سفارش به‌عنوان ارسال‌شده',
                'notification' => [
                    'success' => 'تأمین سفارش به‌عنوان ارسال‌شده علامت‌گذاری شد.',
                    'error' => 'ارسال تأمین سفارش ممکن نشد.',
                ],
            ],
            'fulfil' => [
                'label' => 'علامت‌گذاری به‌عنوان تأمین‌شده',
                'modal_heading' => 'علامت‌گذاری تأمین سفارش به‌عنوان تأمین‌شده',
                'labels' => [
                    'collection' => 'علامت‌گذاری به‌عنوان تحویل‌گرفته‌شده',
                ],
                'notification' => [
                    'success' => 'تأمین سفارش به‌عنوان تأمین‌شده علامت‌گذاری شد.',
                    'error' => 'تأمین سفارش ممکن نشد.',
                ],
            ],
            'cancel' => [
                'label' => 'لغو تأمین سفارش',
                'modal_heading' => 'لغو تأمین سفارش',
                'description' => 'این کار تأمین سفارش را به حالت در انتظار بازمی‌گرداند تا بتوان دوباره آن را پیش برد. هرگونه جزئیات ارسال پاک می‌شود.',
                'notification' => [
                    'success' => 'تأمین سفارش لغو شد.',
                    'error' => 'لغو تأمین سفارش ممکن نشد.',
                ],
            ],
            'change_location' => [
                'label' => 'تغییر موقعیت مکانی',
                'modal_heading' => 'تغییر موقعیت مکانی تأمین سفارش',
                'field' => 'موقعیت مکانی',
                'notification' => [
                    'success' => 'موقعیت مکانی تأمین سفارش به‌روزرسانی شد.',
                    'error' => 'تغییر موقعیت مکانی تأمین سفارش ممکن نشد.',
                ],
            ],
            'return' => [
                'label' => 'بازگشت',
                'notification' => [
                    'success' => 'تأمین سفارش بازگردانده شد.',
                    'error' => 'بازگرداندن تأمین سفارش ممکن نشد.',
                ],
            ],
            'update_status' => [
                'label' => 'به‌روزرسانی وضعیت',
            ],
            'transition' => [
                'modal_heading' => 'تأمین سفارش به‌عنوان :status علامت‌گذاری شود؟',
                'notification' => [
                    'success' => 'وضعیت تأمین سفارش به‌روزرسانی شد.',
                    'error' => 'به‌روزرسانی وضعیت تأمین سفارش ممکن نشد.',
                ],
            ],
            'undo_return' => [
                'label' => 'لغو بازگشت',
                'notification' => [
                    'success' => 'بازگشت لغو شد.',
                    'error' => 'لغو بازگشت ممکن نشد.',
                ],
            ],
            'hold' => [
                'label' => 'تعلیق تأمین سفارش',
                'modal_heading' => 'تعلیق تأمین سفارش',
                'reason' => 'دلیل',
                'note' => 'یادداشت',
                'notification' => [
                    'success' => 'تأمین سفارش در حالت تعلیق قرار گرفت.',
                    'error' => 'تعلیق تأمین سفارش ممکن نشد.',
                ],
            ],
            'release' => [
                'label' => 'رفع تعلیق',
                'notification' => [
                    'success' => 'تعلیق تأمین سفارش رفع شد.',
                    'error' => 'رفع تعلیق تأمین سفارش ممکن نشد.',
                ],
            ],
            'split' => [
                'label' => 'تفکیک',
                'confirm' => 'تفکیک تأمین سفارش',
                'cancel' => 'انصراف',
                'empty' => 'تعدادی را برای تفکیک انتخاب کنید.',
                'modal_heading' => 'تفکیک تأمین سفارش',
                'notification' => [
                    'success' => 'تأمین سفارش تفکیک شد.',
                    'error' => 'تفکیک تأمین سفارش ممکن نشد.',
                ],
            ],
            'merge' => [
                'label' => 'ادغام',
                'confirm' => 'ادغام تأمین سفارش',
                'cancel' => 'انصراف',
                'modal_heading' => 'ادغام تأمین سفارش',
                'description' => 'اقلامی را که می‌خواهید ادغام کنید انتخاب کنید.',
                'target' => 'ادغام با',
                'empty' => 'برای ادغام، اقلام و یک مقصد را انتخاب کنید.',
                'notification' => [
                    'success' => 'تأمین‌های سفارش ادغام شدند.',
                    'error' => 'ادغام تأمین‌های سفارش ممکن نشد.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'تعداد',
            'tracking' => 'رهگیری',
            'tracking_item' => 'رهگیری #:number',
            'unit_price' => 'قیمت واحد',
            'sub_total' => 'جمع جزء',
            'discount_total' => 'جمع تخفیف',
            'total' => 'جمع کل',
            'stock_level' => 'موجودی فعلی انبار: :count',
            'of' => 'از :count',
            'outstanding' => 'باقی‌مانده: :count',
            'tracking_number' => 'شماره رهگیری',
            'tracking_url' => 'نشانی رهگیری',
            'carrier' => 'شرکت حمل‌ونقل',
            'carrier_custom' => 'سفارشی / سایر',
            'tracking_url_help' => 'فقط برای شرکت‌های حمل‌ونقلی لازم است که پیوند رهگیری خودکار ندارند.',
            'shipping_method' => 'روش ارسال',
            'move_quantity' => 'تعداد برای انتقال',
        ],
    ],

    'other_items' => [
        'heading' => 'سایر اقلام',
    ],
];
