<?php

return [
    'dropdown' => [
        'label' => 'Desplegable',
        'form' => [
            'lookups' => [
                'label' => 'Búsquedas',
                'key_label' => 'Etiqueta',
                'value_label' => 'Valor',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Campo de Lista',
    ],
    'text' => [
        'label' => 'Texto',
        'form' => [
            'richtext' => [
                'label' => 'Texto Enriquecido',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Texto Traducido',
        'form' => [
            'richtext' => [
                'label' => 'Texto Enriquecido',
            ],
            'locales' => 'Locales',
        ],
    ],
    'toggle' => [
        'label' => 'Activar/Desactivar',
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
        'label' => 'Archivo',
        'form' => [
            'file_types' => [
                'label' => 'Tipos de Archivo Permitidos',
                'placeholder' => 'Nuevo MIME',
            ],
            'multiple' => [
                'label' => 'Permitir Múltiples Archivos',
            ],
            'min_files' => [
                'label' => 'Mín. Archivos',
            ],
            'max_files' => [
                'label' => 'Máx. Archivos',
            ],
        ],
    ],
];
