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
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
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
            'helper' => 'Jedno pravilo po unosu, na primjer: min:1, max:10',
        ],
    ],
];
