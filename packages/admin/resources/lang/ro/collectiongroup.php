<?php

return [

    'label' => 'Grup de colecții',

    'plural_label' => 'Grupe de colecții',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'collections_count' => [
            'label' => 'Nr. colecții',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Acest grup de colecții nu poate fi șters deoarece are colecții asociate.',
            ],
        ],
    ],
];
