<?php

return [
    'dropdown' => [
        'label' => 'Lista rozwijana',
        'form' => [
            'lookups' => [
                'label' => 'Wyszukiwania',
                'key_label' => 'Etykieta',
                'value_label' => 'Wartość',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Lista',
    ],
    'text' => [
        'label' => 'Pole tekstowe',
        'form' => [
            'richtext' => [
                'label' => 'Tekst sformatowany',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Tekst tłumaczony',
        'form' => [
            'richtext' => [
                'label' => 'Tekst sformatowany',
            ],
            'locales' => 'Języki',
        ],
    ],
    'toggle' => [
        'label' => 'Przełącznik',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Pole numeryczne',
        'form' => [
            'min' => [
                'label' => 'Min.',
            ],
            'max' => [
                'label' => 'Maks.',
            ],
        ],
    ],
    'file' => [
        'label' => 'Plik',
        'form' => [
            'file_types' => [
                'label' => 'Dozwolone typy plików',
                'placeholder' => 'Nowy MIME',
            ],
            'multiple' => [
                'label' => 'Zezwól na wiele plików',
            ],
            'min_files' => [
                'label' => 'Min. plików',
            ],
            'max_files' => [
                'label' => 'Maks. plików',
            ],
            'disk' => [
                'label' => 'Dysk',
            ],
            'directory' => [
                'label' => 'Katalog',
            ],
        ],
    ],
];
