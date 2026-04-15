<?php

return [

    'label' => 'طلب',

    'plural_label' => 'الطلبات',

    'breadcrumb' => [
        'manage' => 'إدارة',
    ],

    'tabs' => [
        'all' => 'الكل',
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
            'label' => 'الحالة',
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
            'label' => 'الحالة',
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

];
