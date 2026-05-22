<?php

return [

    'label' => 'Tip produs',

    'plural_label' => 'Tipuri de produse',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'products_count' => [
            'label' => 'Număr produse',
        ],
        'product_attributes_count' => [
            'label' => 'Atribute produs',
        ],
        'variant_attributes_count' => [
            'label' => 'Atribute variantă',
        ],
    ],

    'tabs' => [
        'product_attributes' => [
            'label' => 'Atribute produs',
        ],
        'variant_attributes' => [
            'label' => 'Atribute variantă',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
    ],

    'attributes' => [
        'no_groups' => 'Nu există grupe de atribute disponibile.',
        'no_attributes' => 'Nu există atribute disponibile.',
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Acest tip de produs nu poate fi șters deoarece are produse asociate.',
            ],
        ],
    ],

];
