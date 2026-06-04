<?php

return [
    'label' => 'Суваг',
    'plural_label' => 'Сувгууд',
    'table' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Өгөгдмөл',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Нэр',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Өгөгдмөл',
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
