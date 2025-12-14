<?php

return [

    'label' => 'Vergi Sınıfı',

    'plural_label' => 'Vergi Sınıfları',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'default' => [
            'label' => 'Varsayılan',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'default' => [
            'label' => 'Varsayılan',
        ],
    ],
    'delete' => [
        'error' => [
            'title' => 'Vergi sınıfı silinemez',
            'body' => 'Bu vergi sınıfının ilişkili ürün varyantları var ve silinemez.',
        ],
    ],
];
