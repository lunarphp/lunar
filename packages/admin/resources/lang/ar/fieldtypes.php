<?php

return [
    'dropdown' => [
        'label' => 'قائمة منسدلة',
        'form' => [
            'lookups' => [
                'label' => 'القيم',
                'key_label' => 'التسمية',
                'value_label' => 'القيمة',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'حقل القائمة',
    ],
    'text' => [
        'label' => 'نص',
        'form' => [
            'richtext' => [
                'label' => 'نص منسق',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'نص مترجم',
        'form' => [
            'richtext' => [
                'label' => 'نص منسق',
            ],
            'locales' => 'اللغات',
        ],
    ],
    'toggle' => [
        'label' => 'مفتاح تبديل',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'رقم',
        'form' => [
            'min' => [
                'label' => 'الحد الأدنى',
            ],
            'max' => [
                'label' => 'الحد الأقصى',
            ],
        ],
    ],
    'file' => [
        'label' => 'ملف',
        'form' => [
            'file_types' => [
                'label' => 'أنواع الملفات المسموح بها',
                'placeholder' => 'نوع MIME جديد',
            ],
            'multiple' => [
                'label' => 'السماح بعدة ملفات',
            ],
            'min_files' => [
                'label' => 'الحد الأدنى للملفات',
            ],
            'max_files' => [
                'label' => 'الحد الأقصى للملفات',
            ],
            'disk' => [
                'label' => 'القرص',
            ],
            'directory' => [
                'label' => 'المجلد',
            ],
        ],
    ],
];
