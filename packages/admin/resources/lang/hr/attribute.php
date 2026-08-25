<?php

return [

    'label' => 'Atribut',

    'plural_label' => 'Atributi',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'type' => [
            'label' => 'Tip',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Tip',
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'description' => [
            'label' => 'Opis',
            'helper' => 'Koristi se za prikaz pomoćnog teksta ispod unosa',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'searchable' => [
            'label' => 'Pretraživo',
        ],
        'filterable' => [
            'label' => 'Filtrirajuće',
        ],
        'required' => [
            'label' => 'Obavezno',
        ],
        'type' => [
            'label' => 'Tip',
        ],
        'validation_rules' => [
            'label' => 'Pravila validacije',
            'helper' => 'Pravila za polje atributa, primjer: min:1|max:10|...',
        ],
        'default_value' => [
            'label' => 'Zadana vrijednost',
            'helper' => 'Primjenjuje se kao početna vrijednost pri stvaranju novog zapisa s ovim atributom.',
        ],
    ],
];
