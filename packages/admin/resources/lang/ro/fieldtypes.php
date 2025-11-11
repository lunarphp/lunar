<?php

return [
    'dropdown' => [
        'label' => 'Listă derulantă',
        'form' => [
            'lookups' => [
                'label' => 'Valori',
                'key_label' => 'Etichetă',
                'value_label' => 'Valoare',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Câmp listă',
    ],
    'text' => [
        'label' => 'Text',
        'form' => [
            'richtext' => [
                'label' => 'Text îmbogățit',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Text tradus',
        'form' => [
            'richtext' => [
                'label' => 'Text îmbogățit',
            ],
            'locales' => 'Locale',
        ],
    ],
    'toggle' => [
        'label' => 'Comutator',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Număr',
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
        'label' => 'Fișier',
        'form' => [
            'file_types' => [
                'label' => 'Tipuri de fișiere permise',
                'placeholder' => 'MIME nou',
            ],
            'multiple' => [
                'label' => 'Permite fișiere multiple',
            ],
            'min_files' => [
                'label' => 'Min. fișiere',
            ],
            'max_files' => [
                'label' => 'Max. fișiere',
            ],
        ],
    ],
];
