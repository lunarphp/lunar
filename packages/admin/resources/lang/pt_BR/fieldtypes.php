<?php

return [
    'dropdown' => [
        'label' => 'Dropdown',
        'form' => [
            'lookups' => [
                'label' => 'Consultas',
                'key_label' => 'Rótulo',
                'value_label' => 'Valor',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Campo de lista',
    ],
    'text' => [
        'label' => 'Texto',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Texto traduzido',
        'form' => [
            'richtext' => [
                'label' => 'Rich Text',
            ],
            'locales' => 'Idiomas',
        ],
    ],
    'toggle' => [
        'label' => 'Alternar',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Número',
        'form' => [
            'min' => [
                'label' => 'Mín.',
            ],
            'max' => [
                'label' => 'Máx.',
            ],
        ],
    ],
    'file' => [
        'label' => 'Arquivo',
        'form' => [
            'file_types' => [
                'label' => 'Tipos de arquivo permitidos',
                'placeholder' => 'Novo MIME',
            ],
            'multiple' => [
                'label' => 'Permitir múltiplos arquivos',
            ],
            'min_files' => [
                'label' => 'Mín. de arquivos',
            ],
            'max_files' => [
                'label' => 'Máx. de arquivos',
            ],
        ],
    ],
];
