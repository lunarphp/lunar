<?php

return [

    'label' => 'Tipo de Produto',

    'plural_label' => 'Tipos de Produto',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'products_count' => [
            'label' => 'Contagem de Produtos',
        ],
        'product_attributes_count' => [
            'label' => 'Atributos do Produto',
        ],
        'variant_attributes_count' => [
            'label' => 'Atributos da Variante',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Atributos do Produto',
        ],
        'variant_attributes' => [
            'label' => 'Atributos da Variante',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nome',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Não há grupos de atributos disponíveis.',
        'no_attributes' => 'Não há atributos disponíveis.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Este tipo de produto não pode ser excluído, pois existem produtos associados.',
            ],
        ],
    ],

];
