<?php

return [
    'label' => 'Atributo',
    'plural_label' => 'Atributos',
    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'description' => [
            'label' => 'Descrição',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'type' => [
            'label' => 'Tipo',
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
            'label' => 'Tipo',
        ],
        'name' => [
            'label' => 'Nome',
        ],
        'description' => [
            'label' => 'Descrição',
            'helper' => 'Use para exibir um texto de ajuda abaixo do campo',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'searchable' => [
            'label' => 'Pesquisável',
        ],
        'filterable' => [
            'label' => 'Filtrável',
        ],
        'required' => [
            'label' => 'Obrigatório',
        ],
        'type' => [
            'label' => 'Tipo',
        ],
        'validation_rules' => [
            'label' => 'Regras de validação',
            'helper' => 'Uma regra por entrada, por exemplo: min:1, max:10',
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
