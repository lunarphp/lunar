<?php

return [
    'dropdown' => [
        'label' => 'Dropdown',
        'form' => [
            'lookups' => [
                'label' => 'Lookups',
                'key_label' => 'Label',
                'value_label' => 'Value',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'List Field',
    ],
    'text' => [
        'label' => 'Text',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Translated Text',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
            'locales' => 'Locales',
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
        'label' => 'Number',
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
        'label' => 'File',
        'form' => [
            'file_types' => [
                'label' => 'Allowed File Types',
                'placeholder' => 'New MIME',
            ],
            'multiple' => [
                'label' => 'Allow Multiple Files',
            ],
            'min_files' => [
                'label' => 'Min. Files',
            ],
            'max_files' => [
                'label' => 'Max. Files',
            ],
        ],
    ],
];
