<?php

return [

    'label' => 'Produkttyp',

    'plural_label' => 'Produkttypen',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'products_count' => [
            'label' => 'Produktanzahl',
        ],
        'product_attributes_count' => [
            'label' => 'Produktattribute',
        ],
        'variant_attributes_count' => [
            'label' => 'Variantenattribute',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Produktattribute',
        ],
        'variant_attributes' => [
            'label' => 'Variantenattribute',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Es sind keine Attributgruppen verfügbar.',
        'no_attributes' => 'Es sind keine Attribute verfügbar.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Dieser Produkttyp kann nicht gelöscht werden, da damit verbundene Produkte vorhanden sind.',
            ],
        ],
    ],

];
