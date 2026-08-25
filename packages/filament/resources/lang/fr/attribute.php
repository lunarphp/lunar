<?php

return [
    'label' => 'Attribut',
    'plural_label' => 'Attributs',
    'table' => [
        'name' => [
            'label' => 'Nom',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'type' => [
            'label' => 'Type',
        ],
    ],
    'form' => [
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'Type',
        ],
        'name' => [
            'label' => 'Nom',
        ],
        'description' => [
            'label' => 'Description',
            'helper' => 'Utilisé pour afficher le texte d\'aide sous l\'entrée',
        ],
        'handle' => [
            'label' => 'Identifiant',
        ],
        'searchable' => [
            'label' => 'Recherchable',
        ],
        'filterable' => [
            'label' => 'Filtrable',
        ],
        'required' => [
            'label' => 'Obligatoire',
        ],
        'type' => [
            'label' => 'Type',
        ],
        'validation_rules' => [
            'label' => 'Règles de validation',
            'helper' => 'Règles pour le champ attribut, exemple : min:1, max:10',
        ],
    ],
];
