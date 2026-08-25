<?php

return [
    'label' => 'Atribut',
    'plural_label' => 'Atribute',
    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'description' => [
            'label' => 'Descriere',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'type' => [
            'label' => 'Tip',
        ],
        'group' => [
            'label' => 'Group',
            'ungrouped' => 'Ungrouped',
        ],
    ],
    'form' => [
        'attribute_group' => [
            'label' => 'Group',
            'placeholder' => 'No group',
        ],
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'Tip',
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'description' => [
            'label' => 'Descriere',
            'helper' => 'Folosit pentru a afișa textul de ajutor sub câmp',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'searchable' => [
            'label' => 'Căutabil',
        ],
        'filterable' => [
            'label' => 'Filtrabil',
        ],
        'required' => [
            'label' => 'Obligatoriu',
        ],
        'type' => [
            'label' => 'Tip',
        ],
        'validation_rules' => [
            'label' => 'Reguli de validare',
            'helper' => 'O regulă per intrare, de exemplu: min:1, max:10',
        ],
    ],

    'actions' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'System attributes cannot be deleted.',
            ],
        ],
    ],
];
