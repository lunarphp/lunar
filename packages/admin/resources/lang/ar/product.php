<?php

return [

    'label' => 'منتج',

    'plural_label' => 'المنتجات',

    'tabs' => [
        'all' => 'الكل',
        'published' => 'منشور',
        'draft' => 'مسودة',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'حالياً في حالة مسودة، هذا المنتج مخفي في جميع واجهات البيع ومجموعات العملاء.',
        ],
        'availability' => [
            'customer_groups' => 'هذا المنتج غير متاح حالياً لجميع مجموعات العملاء.',
            'channels' => 'هذا المنتج غير متاح حالياً لجميع واجهات البيع.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'الحالة',
            'states' => [
                'deleted' => 'محذوف',
                'draft' => 'مسودة',
                'published' => 'منشور',
            ],
        ],
        'name' => [
            'label' => 'الاسم',
        ],
        'brand' => [
            'label' => 'العلامة التجارية',
        ],
        'sku' => [
            'label' => 'رمز التخزين (SKU)',
        ],
        'stock' => [
            'label' => 'المخزون',
        ],
        'producttype' => [
            'label' => 'نوع المنتج',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'تحديث الحالة',
            'heading' => 'تحديث الحالة',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'brand' => [
            'label' => 'العلامة التجارية',
        ],
        'sku' => [
            'label' => 'رمز التخزين (SKU)',
        ],
        'producttype' => [
            'label' => 'نوع المنتج',
        ],
        'status' => [
            'label' => 'الحالة',
            'options' => [
                'published' => [
                    'label' => 'منشور',
                    'description' => 'سيكون هذا المنتج متاحًا لجميع مجموعات العملاء وواجهات البيع المفعلة',
                ],
                'draft' => [
                    'label' => 'Draft',
                    'description' => 'سيكون هذا المنتج مخفيًا في جميع القنوات ومجموعات العملاء',
                ],
            ],
        ],
        'tags' => [
            'label' => 'الوسوم',
            'helper_text' => 'افصل الوسوم بالضغط على Enter أو Tab أو الفاصلة (,)',
        ],
        'collections' => [
            'label' => 'المجموعات',
            'select_collection' => 'اختر مجموعة',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'التوفر',
        ],
        'edit' => [
            'title' => 'المعلومات الأساسية',
        ],
        'identifiers' => [
            'label' => 'معرفات المنتج',
        ],
        'inventory' => [
            'label' => 'المخزون',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'فئة الضريبة',
                ],
                'tax_ref' => [
                    'label' => 'الرقم الضريبي المرجعي',
                    'helper_text' => 'اختياري، للربط مع أنظمة الطرف الثالث.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'الشحن',
        ],
        'variants' => [
            'label' => 'المتغيرات',
        ],
        'collections' => [
            'label' => 'المجموعات',
            'select_collection' => 'اختر مجموعة',
        ],
        'associations' => [
            'label' => 'ارتباطات المنتج',
        ],
    ],

];
