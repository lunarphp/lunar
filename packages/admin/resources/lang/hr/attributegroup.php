<?php

return [

    'label' => 'Grupa atributa',

    'plural_label' => 'Grupe atributa',

    'table' => [
        'attributable_type' => [
            'label' => 'Tip',
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'position' => [
            'label' => 'Pozicija',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Tip',
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'position' => [
            'label' => 'Pozicija',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ovu grupu atributa nije moguće izbrisati jer postoje povezani atributi.',
            ],
        ],
    ],
];
