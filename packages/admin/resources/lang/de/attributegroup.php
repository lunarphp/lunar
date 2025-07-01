<?php

return [

    'label' => 'Attributgruppe',

    'plural_label' => 'Attributgruppen',

    'table' => [
        'attributable_type' => [
            'label' => 'Typ',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Position',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Typ',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'position' => [
            'label' => 'Position',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Diese Attributgruppe kann nicht gelöscht werden, da damit verbundene Attribute vorhanden sind.',
            ],
        ],
    ],
];
