<?php

return [
    'dropdown' => [
        'label' => 'Доош унах цэс',
        'form' => [
            'lookups' => [
                'label' => 'Сонголтууд',
                'key_label' => 'Шошго',
                'value_label' => 'Утга',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Жагсаалтын талбар',
    ],
    'text' => [
        'label' => 'Текст',
        'form' => [
            'richtext' => [
                'label' => 'Баялаг текст',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Орчуулсан текст',
        'form' => [
            'richtext' => [
                'label' => 'Баялаг текст',
            ],
            'locales' => 'Хэлүүд',
        ],
    ],
    'toggle' => [
        'label' => 'Toggle',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Тоо',
        'form' => [
            'min' => [
                'label' => 'Хамгийн бага',
            ],
            'max' => [
                'label' => 'Хамгийн их',
            ],
        ],
    ],
    'file' => [
        'label' => 'Файл',
        'form' => [
            'file_types' => [
                'label' => 'Зөвшөөрөгдсөн файлын төрлүүд',
                'placeholder' => 'Шинэ MIME',
            ],
            'multiple' => [
                'label' => 'Олон файл зөвшөөрөх',
            ],
            'min_files' => [
                'label' => 'Хамгийн бага файл',
            ],
            'max_files' => [
                'label' => 'Хамгийн их файл',
            ],
            'disk' => [
                'label' => 'Диск',
            ],
            'directory' => [
                'label' => 'Хавтас',
            ],
        ],
    ],
];
