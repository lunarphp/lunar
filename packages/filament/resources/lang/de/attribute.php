<?php

return [
    'label' => 'Attribut',
    'plural_label' => 'Attribute',
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Beschreibung',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'type' => [
            'label' => 'Typ',
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
            'label' => 'Typ',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'description' => [
            'label' => 'Beschreibung',
            'helper' => 'Verwenden Sie dies, um den Hilfetext unter dem Eintrag anzuzeigen',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'searchable' => [
            'label' => 'Durchsuchbar',
        ],
        'filterable' => [
            'label' => 'Filterbar',
        ],
        'required' => [
            'label' => 'Erforderlich',
        ],
        'type' => [
            'label' => 'Typ',
        ],
        'validation_rules' => [
            'label' => 'Validierungsregeln',
            'helper' => 'Eine Regel pro Eintrag, zum Beispiel: min:1, max:10',
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
