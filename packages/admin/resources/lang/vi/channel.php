<?php

return [
    'label' => 'Kênh',
    'plural_label' => 'Kênh',
    'table' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'url' => [
            'label' => 'Đường dẫn',
        ],
        'default' => [
            'label' => 'Mặc định',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Tên',
        ],
        'handle' => [
            'label' => 'Định danh',
        ],
        'url' => [
            'label' => 'Đường dẫn',
        ],
        'default' => [
            'label' => 'Mặc định',
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
