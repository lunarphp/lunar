<?php

return [

    'label' => 'Tipo de produto',

    'plural_label' => 'Tipos de produto',

    'table' => [
        'name' => [
            'label' => 'Nome',
        ],
        'products_count' => [
            'label' => 'Qtd. de produtos',
        ],
        'product_attributes_count' => [
            'label' => 'Atributos do produto',
        ],
        'variant_attributes_count' => [
            'label' => 'Atributos da variação',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Atributos do produto',
        ],
        'variant_attributes' => [
            'label' => 'Atributos da variação',
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
                'error_protected' => 'Este tipo de produto não pode ser excluído pois há produtos associados.',
            ],
        ],
    ],

];
