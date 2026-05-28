<?php

return [
    'label' => 'Канал',
    'plural_label' => 'Канали',
    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'url' => [
            'label' => 'URL',
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
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'По подразбиране',
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
