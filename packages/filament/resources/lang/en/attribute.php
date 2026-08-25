<?php

return [

    'label' => 'Attribute',

    'plural_label' => 'Attributes',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'type' => [
            'label' => 'Type',
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
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Description',
            'helper' => 'Use to display the helper text below the entry',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'searchable' => [
            'label' => 'Searchable',
        ],
        'filterable' => [
            'label' => 'Filterable',
        ],
        'required' => [
            'label' => 'Required',
        ],
        'type' => [
            'label' => 'Type',
        ],
        'validation_rules' => [
            'label' => 'Validation Rules',
            'helper' => 'One rule per entry, for example: min:1, max:10',
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
