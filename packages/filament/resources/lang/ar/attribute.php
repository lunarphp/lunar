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
        'group' => [
            'label' => 'Group',
            'ungrouped' => 'Ungrouped',
        ],
    ],
    'form' => [
        'attribute_group' => [
            'label' => 'Group',
            'placeholder' => 'No group',
        ],
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
            'helper' => 'قاعدة واحدة لكل إدخال، على سبيل المثال: min:1, max:10',
        ],
    ],

    'actions' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'System attributes cannot be deleted.',
            ],
        ],
    ],
];
