<?php

return [
    'label' => 'سمة',
    'plural_label' => 'السمات',
    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'description' => [
            'label' => 'الوصف',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'type' => [
            'label' => 'النوع',
        ],
    ],
    'form' => [
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'النوع',
        ],
        'name' => [
            'label' => 'الاسم',
        ],
        'description' => [
            'label' => 'الوصف',
            'helper' => 'يُستخدم لعرض نص المساعدة أسفل الحقل',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'searchable' => [
            'label' => 'قابل للبحث',
        ],
        'filterable' => [
            'label' => 'قابل للتصفية',
        ],
        'required' => [
            'label' => 'مطلوب',
        ],
        'type' => [
            'label' => 'النوع',
        ],
        'validation_rules' => [
            'label' => 'قواعد التحقق',
            'helper' => 'القواعد الخاصة بحقل السمة، مثال: min:1, max:10',
        ],
    ],
];
