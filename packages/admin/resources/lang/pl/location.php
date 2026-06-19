<?php

return [
    'label' => 'Lokalizacja',

    'plural_label' => 'Lokalizacje',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Nie można usunąć tej lokalizacji, ponieważ są do niej przypisane realizacje.',
            ],
        ],
    ],
];
