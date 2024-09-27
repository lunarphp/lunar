<?php

return [

    'label' => 'Groupe d\'attributs',

    'plural_label' => 'Groupes d\'attributs',

    'table' => [
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'position' => [
            'label' => 'Position',
        ],
    ],

    'form' => [
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'position' => [
            'label' => 'Position',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ce groupe d\'attributs ne peut pas être supprimé car des attributs y sont associés.',
            ],
        ],
    ],
];
