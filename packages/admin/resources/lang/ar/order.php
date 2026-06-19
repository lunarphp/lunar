<?php

return [
    'label' => 'طلب',
    'plural_label' => 'الطلبات',
    'breadcrumb' => [
        'manage' => 'إدارة',
    ],
    'transactions' => [
        'capture' => 'تم الاستلام',
        'intent' => 'نية الدفع',
        'refund' => 'تم الاسترجاع',
        'failed' => 'فشل',
    ],
    'table' => [
        'status' => [
            'label' => 'الحالة',
        ],
        'reference' => [
            'label' => 'الرقم المرجعي',
        ],
        'customer_reference' => [
            'label' => 'رقم العميل المرجعي',
        ],
        'customer' => [
            'label' => 'العميل',
        ],
        'tags' => [
            'label' => 'الوسوم',
        ],
        'postcode' => [
            'label' => 'الرمز البريدي',
        ],
        'email' => [
            'label' => 'الايميل',
            'copy_message' => 'تم نسخ الايميل',
        ],
        'phone' => [
            'label' => 'الهاتف',
        ],
        'total' => [
            'label' => 'الإجمالي',
        ],
        'date' => [
            'label' => 'التاريخ',
        ],
        'new_customer' => [
            'label' => 'نوع العميل',
        ],
        'placed_after' => [
            'label' => 'تم الطلب بعد',
        ],
        'placed_before' => [
            'label' => 'تم الطلب قبل',
        ],
    ],
    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'الاسم الأول',
            ],
            'last_name' => [
                'label' => 'اسم العائلة',
            ],
            'line_one' => [
                'label' => 'العنوان 1',
            ],
            'line_two' => [
                'label' => 'العنوان 2',
            ],
            'line_three' => [
                'label' => 'العنوان 3',
            ],
            'company_name' => [
                'label' => 'اسم الشركة',
            ],
            'tax_identifier' => [
                'label' => 'معرف الضريبة',
            ],
            'contact_phone' => [
                'label' => 'الهاتف',
            ],
            'contact_email' => [
                'label' => 'الايميل',
            ],
            'city' => [
                'label' => 'المدينة',
            ],
            'state' => [
                'label' => 'المحافظة',
            ],
            'postcode' => [
                'label' => 'الرمز البريدي',
            ],
            'country_id' => [
                'label' => 'الدولة',
            ],
        ],
        'reference' => [
            'label' => 'الرقم المرجعي',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'transaction' => [
            'label' => 'المعاملة',
        ],
        'amount' => [
            'label' => 'المبلغ',
            'hint' => [
                'less_than_total' => 'أنت على وشك استلام مبلغ أقل من إجمالي قيمة المعاملة',
            ],
        ],
        'notes' => [
            'label' => 'ملاحظات',
        ],
        'confirm' => [
            'label' => 'تأكيد',
            'alert' => 'يتطلب التأكيد',
            'hint' => [
                'capture' => 'يرجى تأكيد رغبتك في استلام هذا الدفع',
                'refund' => 'يرجى تأكيد رغبتك في استرجاع هذا المبلغ',
            ],
        ],
    ],
    'infolist' => [
        'notes' => [
            'label' => 'ملاحظات',
            'placeholder' => 'لا توجد ملاحظات على هذا الطلب',
        ],
        'delivery_instructions' => [
            'label' => 'تعليمات التسليم',
        ],
        'shipping_total' => [
            'label' => 'إجمالي الشحن',
        ],
        'paid' => [
            'label' => 'مدفوع',
        ],
        'refund' => [
            'label' => 'استرجاع',
        ],
        'unit_price' => [
            'label' => 'سعر الوحدة',
        ],
        'quantity' => [
            'label' => 'الكمية',
        ],
        'sub_total' => [
            'label' => 'المجموع الفرعي',
        ],
        'discount_total' => [
            'label' => 'إجمالي الخصم',
        ],
        'total' => [
            'label' => 'الإجمالي',
        ],
        'current_stock_level' => [
            'message' => 'المخزون الحالي: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'وقت الطلب: :count',
        ],
        'status' => [
            'label' => 'Order',
        ],
        'reference' => [
            'label' => 'الرقم المرجعي',
        ],
        'customer_reference' => [
            'label' => 'رقم العميل المرجعي',
        ],
        'channel' => [
            'label' => 'واجهة البيع',
        ],
        'date_created' => [
            'label' => 'تاريخ الإنشاء',
        ],
        'date_placed' => [
            'label' => 'تاريخ الطلب',
        ],
        'new_returning' => [
            'label' => 'جديد / عائد',
        ],
        'new_customer' => [
            'label' => 'عميل جديد',
        ],
        'returning_customer' => [
            'label' => 'عميل عائد',
        ],
        'shipping_address' => [
            'label' => 'عنوان الشحن',
        ],
        'billing_address' => [
            'label' => 'عنوان الفاتورة',
        ],
        'address_not_set' => [
            'label' => 'لم يتم تعيين عنوان',
        ],
        'billing_matches_shipping' => [
            'label' => 'نفس عنوان الشحن',
        ],
        'additional_info' => [
            'label' => 'معلومات إضافية',
        ],
        'no_additional_info' => [
            'label' => 'لا توجد معلومات إضافية',
        ],
        'tags' => [
            'label' => 'الوسوم',
        ],
        'timeline' => [
            'label' => 'الجدول الزمني',
        ],
        'transactions' => [
            'label' => 'المعاملات',
            'placeholder' => 'لا توجد معاملات',
        ],
        'alert' => [
            'requires_capture' => 'هذا الطلب لا يزال يتطلب استلام الدفع.',
            'partially_refunded' => 'تم استرجاع هذا الطلب جزئيًا.',
            'refunded' => 'تم استرجاع هذا الطلب بالكامل.',
        ],
    ],
    'action' => [
        'bulk_update_status' => [
            'label' => 'تحديث الحالة',
            'notification' => 'تم تحديث حالة الطلبات',
        ],
        'update_status' => [
            'label' => 'Update Status',
            'notification' => 'Order status updated',
            'new_status' => [
                'label' => 'الحالة الجديدة',
            ],
            'additional_content' => [
                'label' => 'محتوى إضافي',
            ],
            'additional_email_recipient' => [
                'label' => 'مستلم ايميل إضافي',
                'placeholder' => 'اختياري',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'تحميل PDF',
            'notification' => 'جارٍ تحميل PDF الطلب',
        ],
        'edit_address' => [
            'label' => 'تعديل',
            'notification' => [
                'error' => 'حدث خطأ',
                'billing_address' => [
                    'saved' => 'تم حفظ عنوان الفاتورة',
                ],
                'shipping_address' => [
                    'saved' => 'تم حفظ عنوان الشحن',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'تعديل',
            'form' => [
                'tags' => [
                    'label' => 'الوسوم',
                    'helper_text' => 'افصل الوسوم بالضغط على Enter أو Tab أو الفاصلة (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'استلام الدفع',
            'notification' => [
                'error' => 'حدثت مشكلة أثناء الاستلام',
                'success' => 'تم الاستلام بنجاح',
            ],
        ],
        'refund_payment' => [
            'label' => 'استرجاع',
            'notification' => [
                'error' => 'حدثت مشكلة أثناء الاسترجاع',
                'success' => 'تم الاسترجاع بنجاح',
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
            'notify' => 'Notify customer',
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
