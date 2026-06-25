<?php

return [
    'label' => 'Локация',

    'plural_label' => 'Локации',

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Тази локация не може да бъде изтрита, тъй като към нея има назначени изпълнения.',
            ],
        ],
    ],
];
