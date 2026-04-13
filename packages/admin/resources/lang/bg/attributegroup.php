<?php

return [

    'label' => 'Група атрибути',

    'plural_label' => 'Групи атрибути',

    'table' => [
        'attributable_type' => [
            'label' => 'Тип',
        ],
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'position' => [
            'label' => 'Позиция',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Тип',
        ],
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'position' => [
            'label' => 'Позиция',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Тази група атрибути не може да бъде изтрита, тъй като има свързани атрибути.',
            ],
        ],
    ],
];
