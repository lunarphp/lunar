<?php

return [

    'label' => 'Grupa kolekcija',

    'plural_label' => 'Grupe kolekcija',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'collections_count' => [
            'label' => 'Broj kolekcija',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ovu grupu kolekcija nije moguće izbrisati jer postoje povezane kolekcije.',
            ],
        ],
    ],
];
