<?php

return [
    'label' => 'Groupe de client',
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
                'error_protected' => 'Ce groupe de clients ne peut être supprimé car il y a des clients associés.',
            ],
        ],
    ],
];
