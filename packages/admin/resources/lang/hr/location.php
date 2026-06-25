<?php

return [
    'label' => 'Lokacija',

    'plural_label' => 'Lokacije',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ova lokacija ne može se izbrisati jer su joj dodijeljena ispunjenja.',
            ],
        ],
    ],
];
