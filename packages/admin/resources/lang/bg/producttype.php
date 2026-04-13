<?php

return [

    'label' => 'Тип продукт',

    'plural_label' => 'Типове продукти',

    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'products_count' => [
            'label' => 'Брой продукти',
        ],
        'product_attributes_count' => [
            'label' => 'Атрибути на продукта',
        ],
        'variant_attributes_count' => [
            'label' => 'Атрибути на варианта',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Атрибути на продукта',
        ],
        'variant_attributes' => [
            'label' => 'Атрибути на варианта',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Име',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Няма налични групи атрибути.',
        'no_attributes' => 'Няма налични атрибути.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Този тип продукт не може да бъде изтрит, тъй като има свързани продукти.',
            ],
        ],
    ],

];
