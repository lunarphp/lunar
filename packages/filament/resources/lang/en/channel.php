<?php

return [

    'label' => 'Channel',

    'plural_label' => 'Channels',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'url' => [
            'label' => 'URL',
        ],
        'default' => [
            'label' => 'Default',
        ],
    ],

    'actions' => [
        'delete' => [
            'blocked' => 'The default channel, channels with order history and channels that a region or cart still uses cannot be deleted. Mark the channel Inactive instead.',
        ],
    ],

];
