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
            'blocked' => 'Le canal par défaut, les canaux ayant un historique de commandes et les canaux encore utilisés par une région ou un panier ne peuvent pas être supprimés. Marquez plutôt le canal comme inactif.',
        ],
    ],

];
