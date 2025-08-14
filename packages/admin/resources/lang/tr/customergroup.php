<?php

return [

    'label' => 'Müşteri Grubu',

    'plural_label' => 'Müşteri Grupları',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'default' => [
            'label' => 'Varsayılan',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'default' => [
            'label' => 'Varsayılan',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Bu müşteri grubu silinemez çünkü ilişkili müşteriler var.',
            ],
        ],
    ],
];
