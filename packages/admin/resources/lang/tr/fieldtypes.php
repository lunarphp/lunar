<?php

return [
    'dropdown' => [
        'label' => 'Açılır Menü',
        'form' => [
            'lookups' => [
                'label' => 'Arama Tablosu',
                'key_label' => 'Etiket',
                'value_label' => 'Değer',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Liste Alanı',
    ],
    'text' => [
        'label' => 'Metin',
        'form' => [
            'richtext' => [
                'label' => 'Zengin Metin',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Çevrilmiş Metin',
        'form' => [
            'richtext' => [
                'label' => 'Zengin Metin',
            ],
            'locales' => 'Diller',
        ],
    ],
    'toggle' => [
        'label' => 'Aç/Kapat',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Sayı',
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
        'label' => 'Dosya',
        'form' => [
            'file_types' => [
                'label' => 'İzin Verilen Dosya Türleri',
                'placeholder' => 'Yeni MIME',
            ],
            'multiple' => [
                'label' => 'Birden Fazla Dosyaya İzin Ver',
            ],
            'min_files' => [
                'label' => 'Min. Dosya',
            ],
            'max_files' => [
                'label' => 'Maks. Dosya',
            ],
        ],
    ],
];
