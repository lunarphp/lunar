<?php

return [
    'label' => 'واجهه بيع',
    'plural_label' => 'واجهات البيع',
    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'افتراضي',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'افتراضي',
        ],
    ],
    'actions' => [
        'delete' => [
            'confirm' => 'This permanently deletes the channel and cannot be undone. If you want to stop using it without losing it, mark it Inactive instead.',
            'blocked' => 'This channel has orders associated with it and cannot be deleted — mark it Inactive instead so historical orders keep their context.',
            'disabled_tooltip' => 'Channels with order history can\'t be deleted. Mark Inactive instead.',
        ],
    ],

];
