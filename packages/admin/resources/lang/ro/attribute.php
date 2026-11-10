<?php

return [

    'label' => 'Atribut',

    'plural_label' => 'Atribute',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'description' => [
            'label' => 'Descriere',
        ],
        'handle' => [
            'label' => 'Identificator',
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
            'label' => 'Nume',
        ],
        'description' => [
            'label' => 'Descriere',
            'helper' => 'Folosit pentru a afișa textul de ajutor sub câmp',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'searchable' => [
            'label' => 'Căutabil',
        ],
        'filterable' => [
            'label' => 'Filtrabil',
        ],
        'required' => [
            'label' => 'Obligatoriu',
        ],
        'type' => [
            'label' => 'Tip',
        ],
        'validation_rules' => [
            'label' => 'Reguli de validare',
            'helper' => 'Reguli pentru câmpul atribut, de ex.: min:1|max:10|...',
        ],
        'default_value' => [
            'label' => 'Valoare implicită',
            'helper' => 'Aplicată ca valoare inițială la crearea unui nou înregistrări cu acest atribut.',
        ],
    ],
];
