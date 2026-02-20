<?php

return [

    'label' => 'Terméktípus',

    'plural_label' => 'Terméktípusok',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'products_count' => [
            'label' => 'Termékek száma',
        ],
        'product_attributes_count' => [
            'label' => 'Termék attribútumok',
        ],
        'variant_attributes_count' => [
            'label' => 'Termékváltozat attribútumok',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Termék attribútumok',
        ],
        'variant_attributes' => [
            'label' => 'Termékváltozat attribútumok',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Nincsenek elérhető attribútumcsoportok.',
        'no_attributes' => 'Nincsenek elérhető attribútumok.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'A terméktípus nem törölhető, mert vannak hozzá rendelve termékek.',
            ],
        ],
    ],

];
