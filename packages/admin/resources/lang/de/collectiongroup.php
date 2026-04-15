<?php

return [

    'label' => 'Sammlungsgruppe',

    'plural_label' => 'Sammlungsgruppen',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'collections_count' => [
            'label' => 'Anzahl Sammlungen',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Diese Sammlungsgruppe kann nicht gelöscht werden, da damit verbundene Sammlungen vorhanden sind.',
            ],
        ],
    ],
];
