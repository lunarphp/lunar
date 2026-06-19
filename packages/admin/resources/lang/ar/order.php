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
        'heading' => 'عمليات التنفيذ',
        'unreferenced' => 'عملية التنفيذ #:id',
        'on_hold' => 'معلّقة',
        'empty' => 'لا توجد عمليات تنفيذ بعد.',
        'columns' => [
            'reference' => 'المرجع',
            'state' => 'الحالة',
            'items' => 'العناصر',
            'tracking' => 'التتبّع',
            'shipped_at' => 'تاريخ الشحن',
            'handed_over' => [
                'shipping' => 'تاريخ الشحن',
                'collection' => 'تاريخ الاستلام',
                'digital' => 'تاريخ التوفير',
            ],
            'handed_over_default' => 'تاريخ التنفيذ',
        ],
        'actions' => [
            'more' => 'إجراءات إضافية',
            'notify' => 'إشعار العميل',
            'add_tracking' => [
                'label' => 'إضافة تتبّع',
                'modal_heading' => 'إضافة تتبّع',
                'notification' => [
                    'success' => 'تمت إضافة التتبّع.',
                    'error' => 'تعذّرت إضافة التتبّع.',
                ],
            ],
            'remove_tracking' => [
                'label' => 'إزالة التتبّع',
                'notification' => [
                    'success' => 'تمت إزالة التتبّع.',
                    'error' => 'تعذّرت إزالة التتبّع.',
                ],
            ],
            'create' => [
                'label' => 'إنشاء عملية تنفيذ',
                'modal_heading' => 'إنشاء عملية تنفيذ',
                'empty' => 'تم تنفيذ كل سطر بالفعل.',
                'notification' => [
                    'success' => 'تم إنشاء عملية التنفيذ.',
                    'error' => 'تعذّر إنشاء عملية التنفيذ.',
                ],
            ],
            'ship' => [
                'label' => 'وضع علامة كمشحون',
                'modal_heading' => 'وضع علامة على عملية التنفيذ كمشحونة',
                'notification' => [
                    'success' => 'تم وضع علامة على عملية التنفيذ كمشحونة.',
                    'error' => 'تعذّر شحن عملية التنفيذ.',
                ],
            ],
            'fulfil' => [
                'label' => 'وضع علامة كمنفّذة',
                'modal_heading' => 'وضع علامة على عملية التنفيذ كمنفّذة',
                'labels' => [
                    'collection' => 'وضع علامة كمستلَمة',
                ],
                'notification' => [
                    'success' => 'تم وضع علامة على عملية التنفيذ كمنفّذة.',
                    'error' => 'تعذّر تنفيذ عملية التنفيذ.',
                ],
            ],
            'cancel' => [
                'label' => 'إلغاء عملية التنفيذ',
                'modal_heading' => 'إلغاء عملية التنفيذ',
                'description' => 'يؤدي ذلك إلى إعادة عملية التنفيذ إلى حالة قيد الانتظار بحيث يمكن متابعتها مجدداً. يتم مسح أي تفاصيل شحن.',
                'notification' => [
                    'success' => 'تم إلغاء عملية التنفيذ.',
                    'error' => 'تعذّر إلغاء عملية التنفيذ.',
                ],
            ],
            'change_location' => [
                'label' => 'تغيير الموقع',
                'modal_heading' => 'تغيير موقع عملية التنفيذ',
                'field' => 'الموقع',
                'notification' => [
                    'success' => 'تم تحديث موقع عملية التنفيذ.',
                    'error' => 'تعذّر تغيير موقع عملية التنفيذ.',
                ],
            ],
            'return' => [
                'label' => 'إرجاع',
                'notification' => [
                    'success' => 'تم إرجاع عملية التنفيذ.',
                    'error' => 'تعذّر إرجاع عملية التنفيذ.',
                ],
            ],
            'update_status' => [
                'label' => 'تحديث الحالة',
            ],
            'transition' => [
                'modal_heading' => 'وضع علامة على عملية التنفيذ بأنها :status؟',
                'notification' => [
                    'success' => 'تم تحديث حالة عملية التنفيذ.',
                    'error' => 'تعذّر تحديث حالة عملية التنفيذ.',
                ],
            ],
            'undo_return' => [
                'label' => 'التراجع عن الإرجاع',
                'notification' => [
                    'success' => 'تم التراجع عن الإرجاع.',
                    'error' => 'تعذّر التراجع عن الإرجاع.',
                ],
            ],
            'hold' => [
                'label' => 'تعليق عملية التنفيذ',
                'modal_heading' => 'تعليق عملية التنفيذ',
                'reason' => 'السبب',
                'note' => 'ملاحظة',
                'notification' => [
                    'success' => 'تم تعليق عملية التنفيذ.',
                    'error' => 'تعذّر تعليق عملية التنفيذ.',
                ],
            ],
            'release' => [
                'label' => 'رفع التعليق',
                'notification' => [
                    'success' => 'تم رفع التعليق عن عملية التنفيذ.',
                    'error' => 'تعذّر رفع التعليق عن عملية التنفيذ.',
                ],
            ],
            'split' => [
                'label' => 'تقسيم',
                'confirm' => 'تقسيم عملية التنفيذ',
                'cancel' => 'إلغاء',
                'empty' => 'حدّد كمية لتقسيمها.',
                'modal_heading' => 'تقسيم عملية التنفيذ',
                'notification' => [
                    'success' => 'تم تقسيم عملية التنفيذ.',
                    'error' => 'تعذّر تقسيم عملية التنفيذ.',
                ],
            ],
            'merge' => [
                'label' => 'دمج',
                'confirm' => 'دمج عملية التنفيذ',
                'cancel' => 'إلغاء',
                'modal_heading' => 'دمج عملية التنفيذ',
                'description' => 'حدّد العناصر التي ترغب في دمجها.',
                'target' => 'الدمج مع',
                'empty' => 'حدّد العناصر ووجهة للدمج.',
                'notification' => [
                    'success' => 'تم دمج عمليات التنفيذ.',
                    'error' => 'تعذّر دمج عمليات التنفيذ.',
                ],
            ],
        ],
        'fields' => [
            'quantity' => 'الكمية',
            'tracking' => 'التتبّع',
            'tracking_item' => 'التتبّع #:number',
            'unit_price' => 'سعر الوحدة',
            'sub_total' => 'المجموع الفرعي',
            'discount_total' => 'إجمالي الخصم',
            'total' => 'الإجمالي',
            'stock_level' => 'مستوى المخزون الحالي: :count',
            'of' => 'من :count',
            'outstanding' => 'المتبقي: :count',
            'tracking_number' => 'رقم التتبّع',
            'tracking_url' => 'رابط التتبّع',
            'carrier' => 'شركة النقل',
            'carrier_custom' => 'مخصص / أخرى',
            'tracking_url_help' => 'مطلوب فقط لشركات النقل التي لا تتوفر لديها رابط تتبّع تلقائي.',
            'shipping_method' => 'طريقة الشحن',
            'move_quantity' => 'الكمية المراد نقلها',
        ],
    ],

    'other_items' => [
        'heading' => 'عناصر أخرى',
    ],
];
