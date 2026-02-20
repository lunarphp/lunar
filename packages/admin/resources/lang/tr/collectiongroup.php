<?php

return [

    'label' => 'Koleksiyon Grubu',

    'plural_label' => 'Koleksiyon Grupları',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
        'collections_count' => [
            'label' => 'Koleksiyon Sayısı',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'handle' => [
            'label' => 'Tanımlayıcı',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Bu koleksiyon grubu silinemez çünkü ilişkili koleksiyonlar var.',
            ],
        ],
    ],
];
