<?php

return [

    'label' => 'Özellik Grubu',

    'plural_label' => 'Özellik Grupları',

    'table' => [
        'attributable_type' => [
            'label' => 'Tür',
        ],
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'position' => [
            'label' => 'Pozisyon',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Tür',
        ],
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'position' => [
            'label' => 'Pozisyon',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Bu özellik grubu silinemez çünkü ilişkili özellikler var.',
            ],
        ],
    ],
];
