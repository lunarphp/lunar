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
    ],

    'form' => [
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
            'helper' => 'Regras para o campo do atributo, exemplo: min:1|max:10|...',
        ],
    ],
];
