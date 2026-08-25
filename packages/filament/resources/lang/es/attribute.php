<?php

return [
    'label' => 'Atributo',
    'plural_label' => 'Atributos',
    'table' => [
        'name' => [
            'label' => 'Nombre',
        ],
        'description' => [
            'label' => 'Descripción',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'type' => [
            'label' => 'Tipo',
        ],
        'group' => [
            'label' => 'Grupo',
            'ungrouped' => 'Sin grupo',
        ],
    ],
    'form' => [
        'attribute_group' => [
            'label' => 'Grupo',
            'placeholder' => 'Sin grupo',
        ],
        'model_types' => [
            'label' => 'Applies to',
            'product_and_variant_invalid' => 'An attribute cannot apply to both Product and Product Variant.',
        ],
        'attributable_type' => [
            'label' => 'Tipo',
        ],
        'name' => [
            'label' => 'Nombre',
        ],
        'description' => [
            'label' => 'Descripción',
            'helper' => 'Se usa para mostrar el texto de ayuda debajo de la entrada',
        ],
        'handle' => [
            'label' => 'Identificador',
        ],
        'searchable' => [
            'label' => 'Buscable',
        ],
        'filterable' => [
            'label' => 'Filtrable',
        ],
        'required' => [
            'label' => 'Requerido',
        ],
        'type' => [
            'label' => 'Tipo',
        ],
        'validation_rules' => [
            'label' => 'Reglas de validación',
            'helper' => 'Una regla por entrada, por ejemplo: min:1, max:10',
        ],
    ],

    'actions' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Los atributos del sistema no se pueden eliminar.',
            ],
        ],
    ],
];
