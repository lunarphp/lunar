<?php

return [
    'dropdown' => [
        'label' => 'Падащо меню',
        'form' => [
            'lookups' => [
                'label' => 'Списък стойности',
                'key_label' => 'Етикет',
                'value_label' => 'Стойност',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Списъчно поле',
    ],
    'text' => [
        'label' => 'Текст',
        'form' => [
            'richtext' => [
                'label' => 'Богат текст',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Преведен текст',
        'form' => [
            'richtext' => [
                'label' => 'Богат текст',
            ],
            'locales' => 'Езици',
        ],
    ],
    'toggle' => [
        'label' => 'Превключвател',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Число',
        'form' => [
            'min' => [
                'label' => 'Мин.',
            ],
            'max' => [
                'label' => 'Макс.',
            ],
        ],
    ],
    'file' => [
        'label' => 'Файл',
        'form' => [
            'file_types' => [
                'label' => 'Позволени типове файлове',
                'placeholder' => 'Нов MIME тип',
            ],
            'multiple' => [
                'label' => 'Позволяване на множество файлове',
            ],
            'min_files' => [
                'label' => 'Мин. брой файлове',
            ],
            'max_files' => [
                'label' => 'Макс. брой файлове',
            ],
            'disk' => [
                'label' => 'Диск',
            ],
            'directory' => [
                'label' => 'Директория',
            ],
        ],
    ],
];
