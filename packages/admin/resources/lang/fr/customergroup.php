<?php

return [

    'label' => 'Groupe de clients',

    'plural_label' => 'Groupes de clients',

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
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
        'default' => [
            'label' => 'Par défaut',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ce groupe de clients ne peut pas être supprimé car des clients y sont associés.',
            ],
        ],
    ],
];
