<?php

return [
    'label' => 'Groupe d\'attributs',
    'plural_label' => 'Groupes de attributs',
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
                'error_protected' => 'Ce groupe d\'attributs ne peut pas être supprimé car il y a des attributs associés.',
            ],
        ],
    ],
];
