<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
    'label' => 'متغير المنتج',
    'plural_label' => 'متغيرات المنتجات',
    'pages' => [
        'edit' => [
            'title' => 'المعلومات الأساسية',
        ],
        'media' => [
            'title' => 'الصور',
            'form' => [
                'no_selection' => [
                    'label' => 'ليس لديك صورة محددة حاليًا لهذا المتغير.',
                ],
                'no_media_available' => [
                    'label' => 'لا توجد وسائط متاحة حاليًا لهذا المنتج.',
                ],
                'images' => [
                    'label' => 'Primary Image',
                    'helper_text' => 'اختر صورة المنتج التي تمثل هذا المتغير.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'المعرفات',
        ],
        'inventory' => [
            'title' => 'المخزون',
        ],
        'shipping' => [
            'title' => 'الشحن',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'رمز التخزين (SKU)',
        ],
        'gtin' => [
            'label' => 'الرقم العالمي للمنتج (GTIN)',
        ],
        'mpn' => [
            'label' => 'رقم القطعة لدى الشركة المصنعة (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'stock' => [
            'tooltip' => 'Units on hand at your default location. Changing this records a stock adjustment.',
            'label' => 'متوفر في المخزون',
        ],
        'backorder' => [
            'tooltip' => 'How many units you will accept orders for beyond stock on hand. Used only when Selling Policy allows backorders.',
            'label' => 'طلب مسبق',
        ],
        'purchasable' => [
            'tooltip' => 'When this variant can be purchased. In Stock sells only while units are available; In Stock or On Backorder also sells the backorder allowance; Always ignores stock entirely.',
            'label' => 'Selling Policy',
            'options' => [
                'always' => 'دائمًا',
                'in_stock' => 'متوفر في المخزون',
                'in_stock_or_on_backorder' => 'متوفر في المخزون أو طلب مسبق',
            ],
        ],
        'unit_quantity' => [
            'label' => 'كمية الوحدة',
            'helper_text' => 'كم عدد العناصر الفردية التي تشكل وحدة واحدة.',
        ],
        'min_quantity' => [
            'label' => 'الحد الأدنى للكمية',
            'helper_text' => 'الحد الأدنى للكمية التي يمكن شراؤها من متغير المنتج في عملية شراء واحدة.',
        ],
        'quantity_increment' => [
            'label' => 'زيادة الكمية',
            'helper_text' => 'يجب شراء متغير المنتج بمضاعفات هذه الكمية.',
        ],
        'tax_class_id' => [
            'label' => 'فئة الضريبة',
        ],
        'shippable' => [
            'label' => 'قابل للشحن',
        ],
        'length_value' => [
            'label' => 'الطول',
        ],
        'length_unit' => [
            'label' => 'وحدة الطول',
        ],
        'width_value' => [
            'label' => 'العرض',
        ],
        'width_unit' => [
            'label' => 'وحدة العرض',
        ],
        'height_value' => [
            'label' => 'الارتفاع',
        ],
        'height_unit' => [
            'label' => 'وحدة الارتفاع',
        ],
        'weight_value' => [
            'label' => 'الوزن',
        ],
        'weight_unit' => [
            'label' => 'وحدة الوزن',
        ],
    ],
];
