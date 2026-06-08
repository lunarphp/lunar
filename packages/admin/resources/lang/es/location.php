<?php

return [
    'label' => 'Location',

    'plural_label' => 'Locations',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'This location can not be deleted as there are fulfilments assigned to it.',
            ],
        ],
    ],
];
