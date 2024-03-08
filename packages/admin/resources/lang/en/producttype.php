<?php

return [

    'label' => 'Product Type',

    'plural_label' => 'Product Types',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'products_count' => [
            'label' => 'Product count',
        ],
        'product_attributes_count' => [
            'label' => 'Product Attributes',
        ],
        'variant_attributes_count' => [
            'label' => 'Variant Attributes',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Product Attributes',
        ],
        'variant_attributes' => [
            'label' => 'Variant Attributes',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
    ],

    'attributes' => [
        'no_groups' => 'There are no attribute groups available.',
        'no_attributes' => 'There are no attributes available.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'This product type can not be deleted as there are products associated.',
            ],
        ],
    ],

];
