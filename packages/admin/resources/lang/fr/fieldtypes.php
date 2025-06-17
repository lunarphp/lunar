<?php

return [
    'dropdown' => [
        'label' => 'Liste déroulante',
        'form' => [
            'lookups' => [
                'label' => 'Recherches',
                'key_label' => 'Étiquette',
                'value_label' => 'Valeur',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Champ de liste',
    ],
    'text' => [
        'label' => 'Texte',
        'form' => [
            'richtext' => [
                'label' => 'Texte enrichi',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Texte traduit',
        'form' => [
            'richtext' => [
                'label' => 'Texte enrichi',
            ],
            'locales' => 'Locales',
        ],
    ],
    'toggle' => [
        'label' => 'Interrupteur',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Nombre',
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
        'label' => 'Fichier',
        'form' => [
            'file_types' => [
                'label' => 'Types de fichiers autorisés',
                'placeholder' => 'Nouveau MIME',
            ],
            'multiple' => [
                'label' => 'Autoriser plusieurs fichiers',
            ],
            'min_files' => [
                'label' => 'Fichiers min.',
            ],
            'max_files' => [
                'label' => 'Fichiers max.',
            ],
        ],
    ],
];
