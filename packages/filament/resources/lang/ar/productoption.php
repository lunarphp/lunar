<?php

return [

    'label' => 'خيار المنتج',

    'plural_label' => 'خيارات المنتج',

    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'label' => [
            'label' => 'التسمية',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'shared' => [
            'label' => 'مشترك',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'label' => [
            'label' => 'التسمية',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'تم حفظ متغيرات المنتج',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'إلغاء',
                ],
                'save-options' => [
                    'label' => 'حفظ الخيارات',
                ],
                'add-shared-option' => [
                    'label' => 'إضافة خيار مشترك',
                    'form' => [
                        'product_option' => [
                            'label' => 'خيار المنتج',
                        ],
                        'no_shared_components' => [
                            'label' => 'لا توجد خيارات مشتركة متاحة.',
                        ],
                        'preselect' => [
                            'label' => 'تحديد جميع القيم مسبقًا افتراضيًا.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'إضافة خيار',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'لا توجد خيارات منتج تم تكوينها',
                    'description' => 'أضف خيار منتج مشترك أو مقيد لبدء إنشاء بعض المتغيرات.',
                ],
            ],
            'options-table' => [
                'title' => 'خيارات المنتج',
                'configure-options' => [
                    'label' => 'تكوين الخيارات',
                ],
                'table' => [
                    'option' => [
                        'label' => 'الخيار',
                    ],
                    'values' => [
                        'label' => 'القيم',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'متغيرات المنتج',
                'actions' => [
                    'create' => [
                        'label' => 'إنشاء متغير',
                    ],
                    'edit' => [
                        'label' => 'تعديل',
                    ],
                    'delete' => [
                        'label' => 'حذف',
                    ],
                ],
                'empty' => [
                    'heading' => 'لا توجد متغيرات تم تكوينها',
                ],
                'table' => [
                    'new' => [
                        'label' => 'جديد',
                    ],
                    'option' => [
                        'label' => 'الخيار',
                    ],
                    'sku' => [
                        'label' => 'رمز التخزين (SKU)',
                    ],
                    'price' => [
                        'label' => 'السعر',
                    ],
                    'stock' => [
                        'label' => 'المخزون',
                    ],
                ],
            ],
        ],
    ],

];
