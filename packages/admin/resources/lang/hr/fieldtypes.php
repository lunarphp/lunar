<?php

return [
    'dropdown' => [
        'label' => 'Padajući izbornik',
        'form' => [
            'lookups' => [
                'label' => 'Vrijednosti',
                'key_label' => 'Oznaka',
                'value_label' => 'Vrijednost',
            ],
        ],
    ],
    'listfield' => [
        'label' => 'Polje s popisom',
    ],
    'text' => [
        'label' => 'Tekst',
        'form' => [
            'richtext' => [
                'label' => 'Obogaćeni tekst',
            ],
        ],
    ],
    'translatedtext' => [
        'label' => 'Prevedeni tekst',
        'form' => [
            'richtext' => [
                'label' => 'Obogaćeni tekst',
            ],
            'locales' => 'Jezici',
        ],
    ],
    'toggle' => [
        'label' => 'Gumb za prebacivanje',
    ],
    'youtube' => [
        'label' => 'YouTube',
    ],
    'vimeo' => [
        'label' => 'Vimeo',
    ],
    'number' => [
        'label' => 'Broj',
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
        'label' => 'Datoteka',
    ],
];
