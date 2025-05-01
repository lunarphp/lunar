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
    ],

    'form' => [
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
            'helper' => 'Reglas para el campo de atributo, ejemplo: min:1|max:10|...',
        ],
    ],
];
