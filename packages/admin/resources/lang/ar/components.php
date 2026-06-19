<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'تم تحديث الوسوم',
        ],
    ],
    'activity-log' => [
        'input' => [
            'placeholder' => 'أضف تعليق',
        ],
        'action' => [
            'add-comment' => 'أضف تعليق',
        ],
        'system' => 'النظام',
        'partials' => [
            'orders' => [
                'order_created' => 'تم إنشاء الطلب',
                'status_change' => 'تم تحديث الحالة',
                'order_closed' => 'تم إغلاق الطلب',
                'order_reopened' => 'تمت إعادة فتح الطلب',
                'order_cancelled' => 'تم إلغاء الطلب (:reason)',
                'order_cancelled_no_reason' => 'تم إلغاء الطلب',
                'fulfilment_state' => 'تم وضع علامة على عملية التنفيذ #:id بأنها :state',
                'fulfilment_held' => 'تم تعليق عملية التنفيذ #:id (:reason)',
                'fulfilment_held_no_reason' => 'تم تعليق عملية التنفيذ #:id',
                'fulfilment_released' => 'تم رفع التعليق عن عملية التنفيذ #:id',

                'capture' => 'تم سحب :amount من البطاقة المنتهية بـ :last_four',
                'authorized' => 'تم التفويض بمبلغ :amount على البطاقة المنتهية بـ :last_four',
                'refund' => 'تم رد :amount على البطاقة المنتهية بـ :last_four',
                'address' => 'تم تحديث :type',
                'billingAddress' => 'عنوان الفاتورة',
                'shippingAddress' => 'عنوان الشحن',
            ],
            'update' => [
                'updated' => 'تم تحديث :model',
            ],
            'create' => [
                'created' => 'تم إنشاء :model',
            ],
            'tags' => [
                'updated' => 'تم تحديث العلامات',
                'added' => 'تمت الإضافة',
                'removed' => 'تمت الإزالة',
            ],
        ],
        'notification' => [
            'comment_added' => 'تمت إضافة التعليق',
        ],
    ],
    'forms' => [
        'youtube' => [
            'helperText' => 'أدخل معرف فيديو YouTube. مثال: dQw4w9WgXcQ',
        ],
    ],
    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'المجموعة الرئيسية',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'تم إعادة ترتيب المجموعات',
            ],
            'node-expanded' => [
                'danger' => 'تعذر تحميل المجموعات',
            ],
            'delete' => [
                'danger' => 'تعذر حذف المجموعة',
            ],
        ],
    ],
    'product-options-list' => [
        'add-option' => [
            'label' => 'أضف خيار',
        ],
        'delete-option' => [
            'label' => 'حذف الخيار',
        ],
        'remove-shared-option' => [
            'label' => 'إزالة الخيار المشترك',
        ],
        'add-value' => [
            'label' => 'أضف قيمة أخرى',
        ],
        'name' => [
            'label' => 'الاسم',
        ],
        'values' => [
            'label' => 'القيم',
        ],
    ],
];
