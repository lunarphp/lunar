<?php

return [
    'dropdown' => [
        'label' => 'Danh sách thả xuống',
        'form' => [
            'lookups' => [
                'label' => 'Tra cứu',
                'key_label' => 'Nhãn',
                'value_label' => 'Giá trị',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Trường danh sách',
    ],
    'text' => [
        'label' => 'Văn bản',
        'form' => [
            'richtext' => [
                'label' => 'Văn bản định dạng',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Văn bản đã dịch',
        'form' => [
            'richtext' => [
                'label' => 'Văn bản định dạng',
            ],
            'locales' => 'Ngôn ngữ',
        ],
    ],
    'toggle' => [
        'label' => 'Nút chuyển đổi',
    ],
    'youtube' => [
        'label' => 'Youtube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Số',
        'form' => [
            'min' => [
                'label' => 'Tối thiểu',
            ],
            'max' => [
                'label' => 'Tối đa',
            ],
        ],
    ],
    'file' => [
        'label' => 'Tệp',
        'form' => [
            'file_types' => [
                'label' => 'Loại tệp cho phép',
                'placeholder' => 'MIME mới',
            ],
            'multiple' => [
                'label' => 'Cho phép nhiều tệp',
            ],
            'min_files' => [
                'label' => 'Số tệp tối thiểu',
            ],
            'max_files' => [
                'label' => 'Số tệp tối đa',
            ],
        ],
    ],
];
