<?php

return [

    'label' => 'Група колекции',

    'plural_label' => 'Групи колекции',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'collections_count' => [
            'label' => 'Брой колекции',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Тази група колекции не може да бъде изтрита, тъй като има свързани колекции.',
            ],
        ],
    ],
];
