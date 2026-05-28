<?php

return [
    'dropdown' => [
        'label' => 'Dropdown',
        'form' => [
            'lookups' => [
                'label' => 'Lookups',
                'key_label' => 'Bezeichnung',
                'value_label' => 'Wert',
            ],
        ],
    ],
    'list' => [
        'label' => 'Listenfeld',
    ],
    'text' => [
        'label' => 'Text',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
        ],
    ],
    'translated_text' => [
        'label' => 'Übersetzter Text',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
            'locales' => 'Sprachen',
        ],
    ],
    'toggle' => [
        'label' => 'Umschalter',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Nummer',
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
        'label' => 'Datei',
        'form' => [
            'file_types' => [
                'label' => 'Erlaubte Dateitypen',
                'placeholder' => 'Neuer MIME',
            ],
            'multiple' => [
                'label' => 'Mehrere Dateien erlauben',
            ],
            'min_files' => [
                'label' => 'Min. Dateien',
            ],
            'max_files' => [
                'label' => 'Max. Dateien',
            ],
            'disk' => [
                'label' => 'Datenträger',
            ],
            'directory' => [
                'label' => 'Verzeichnis',
            ],
        ],
    ],
];
