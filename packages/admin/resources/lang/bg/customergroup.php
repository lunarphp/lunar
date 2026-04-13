<?php

return [

    'label' => 'Клиентска група',

    'plural_label' => 'Клиентски групи',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'default' => [
            'label' => 'По подразбиране',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'default' => [
            'label' => 'По подразбиране',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Тази клиентска група не може да бъде изтрита, тъй като има свързани клиенти.',
            ],
        ],
    ],
];
