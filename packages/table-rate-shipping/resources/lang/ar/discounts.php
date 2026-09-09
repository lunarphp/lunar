<?php

return [
    'shipping_discount' => [
        'name' => 'سعر الشحن',
        'form' => [
            'methods' => [
                'label' => 'طرق الشحن',
                'add_label' => 'إضافة قاعدة',
            ],
            'shipping_method_id' => [
                'label' => 'طريقة الشحن',
                'placeholder' => 'أي طريقة شحن',
            ],
            'type' => [
                'label' => 'نوع الخصم',
                'options' => [
                    'fixed' => 'سعر ثابت',
                    'percentage' => 'خصم نسبة مئوية',
                ],
            ],
            'percentage' => [
                'label' => 'نسبة الخصم (%)',
            ],
        ],
        'panel' => [
            'rules' => 'القواعد',
            'rules_description' => 'تنطبق كل قاعدة على طريقة شحن واحدة، أو على جميع الطرق عند عدم اختيار أي طريقة. تكون الأولوية للطريقة المحددة على القاعدة العامة.',
            'add_rule' => 'إضافة قاعدة',
            'remove_rule' => 'إزالة القاعدة',
            'no_rules' => 'لا توجد قواعد حتى الآن. أضف قاعدة لتغيير تكلفة الشحن.',
            'method' => 'طريقة الشحن',
            'method_any' => 'أي طريقة شحن',
            'type' => 'التأثير',
            'type_fixed' => 'تحديد سعر الشحن',
            'type_percentage' => 'خصم نسبة مئوية',
            'percentage' => 'نسبة الخصم (%)',
            'prices' => 'يصبح سعر الشحن',
            'prices_hint' => 'يدفع العميل هذا المبلغ للشحن. اترك العملة فارغة للإبقاء على سعرها دون تغيير؛ أدخل 0 للشحن المجاني.',
        ],
        'summary_free' => 'شحن مجاني',
        'summary_percentage' => 'خصم :percentage% على الشحن',
        'summary_from' => 'الشحن من :amount',
    ],
];
