<?php

return [
    'dropdown' => [
        'label' => 'Lenyíló lista',
        'form' => [
            'lookups' => [
                'label' => 'Értékkészlet',
                'key_label' => 'Címke',
                'value_label' => 'Érték',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Lista mező',
    ],
    'text' => [
        'label' => 'Szöveg',
        'form' => [
            'richtext' => [
                'label' => 'Formázott szöveg',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Többnyelvű szöveg',
        'form' => [
            'richtext' => [
                'label' => 'Formázott szöveg',
            ],
            'locales' => 'Nyelvek',
        ],
    ],
    'toggle' => [
        'label' => 'Kapcsoló',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Szám',
        'form' => [
            'min' => [
                'label' => 'Min.',
            ],
            'max' => [
                'label' => 'Max.',
            ],
        ],
    ],
    'file' => [
        'label' => 'Fájl',
        'form' => [
            'file_types' => [
                'label' => 'Engedélyezett fájltípusok',
                'placeholder' => 'Új MIME',
            ],
            'multiple' => [
                'label' => 'Több fájl engedélyezése',
            ],
            'min_files' => [
                'label' => 'Min. fájlok száma',
            ],
            'max_files' => [
                'label' => 'Max. fájlok száma',
            ],
        ],
    ],
];
