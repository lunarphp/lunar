<?php

return [

    'label' => 'Producttype',

    'plural_label' => 'Producttypen',

    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'products_count' => [
            'label' => 'Aantal producten',
        ],
        'product_attributes_count' => [
            'label' => 'Productattributen',
        ],
        'variant_attributes_count' => [
            'label' => 'Variantattributen',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Productattributen',
        ],
        'variant_attributes' => [
            'label' => 'Variantattributen',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Er zijn geen attributengroepen beschikbaar.',
        'no_attributes' => 'Er zijn geen attributen beschikbaar.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Dit producttype kan niet worden verwijderd omdat er producten aan zijn gekoppeld.',
            ],
        ],
    ],

];
