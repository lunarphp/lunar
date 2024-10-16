<?php

return [
    'dropdown' => [
        'label' => 'Keuzelijst',
        'form' => [
            'lookups' => [
                'label' => 'Opzoekingen',
                'key_label' => 'Label',
                'value_label' => 'Waarde',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Lijstveld',
    ],
    'text' => [
        'label' => 'Tekst',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Vertaald Tekst',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
            'locales' => 'Talen',
        ],
    ],
    'toggle' => [
        'label' => 'Schakelaar',
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
        'label' => 'Bestand',
        'form' => [
            'file_types' => [
                'label' => 'Toegestane Bestandstypen',
                'placeholder' => 'Nieuwe MIME',
            ],
            'multiple' => [
                'label' => 'Meerdere Bestanden Toestaan',
            ],
            'min_files' => [
                'label' => 'Min. Bestanden',
            ],
            'max_files' => [
                'label' => 'Max. Bestanden',
            ],
        ],
    ],
];
