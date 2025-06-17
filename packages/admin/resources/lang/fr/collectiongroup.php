<?php

return [

    'label' => 'Groupe de collections',

    'plural_label' => 'Groupes de collections',

    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'collections_count' => [
            'label' => 'Nbre de collections',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nom',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Ce groupe de collections ne peut pas être supprimé car des collections y sont associées.',
            ],
        ],
    ],
];
