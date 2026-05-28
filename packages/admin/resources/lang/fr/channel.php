<?php

return [
    'label' => 'Canal',
    'plural_label' => 'Canaux',
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Par défaut',
        ],
    ],
    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Par défaut',
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
