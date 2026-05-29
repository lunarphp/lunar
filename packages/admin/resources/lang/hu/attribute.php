<?php

return [

    'label' => 'Attribútum',

    'plural_label' => 'Attribútumok',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'description' => [
            'label' => 'Leírás',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'type' => [
            'label' => 'Típus',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Típus',
        ],
        'name' => [
            'label' => 'Név',
        ],
        'description' => [
            'label' => 'Leírás',
            'helper' => 'Használja a bejegyzés alatt a súgó szöveg megjelenítéséhez.',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'searchable' => [
            'label' => 'Kereshető',
        ],
        'filterable' => [
            'label' => 'Szűrhető',
        ],
        'required' => [
            'label' => 'Kötelező',
        ],
        'type' => [
            'label' => 'Típus',
        ],
        'validation_rules' => [
            'label' => 'Érvényesítési szabályok',
            'helper' => 'Szabályok az attribútum mezőhöz, pl.: min:1|max:10|...',
        ],
        'default_value' => [
            'label' => 'Alapértelmezett érték',
            'helper' => 'Kezdeti értékként kerül alkalmazásra új rekord létrehozásakor ezzel az attribútummal.',
        ],
    ],
];