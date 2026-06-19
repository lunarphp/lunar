<?php

return [
    'label' => 'Standort',

    'plural_label' => 'Standorte',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Dieser Standort kann nicht gelöscht werden, da ihm Fulfillments zugewiesen sind.',
            ],
        ],
    ],
];
