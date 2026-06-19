<?php

return [
    'label' => 'Telephely',

    'plural_label' => 'Telephelyek',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ez a telephely nem törölhető, mert teljesítések vannak hozzárendelve.',
            ],
        ],
    ],
];
