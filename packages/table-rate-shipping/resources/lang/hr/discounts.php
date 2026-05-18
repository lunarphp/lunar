<?php

return [
    'shipping_discount' => [
        'name' => 'Cijena dostave',
        'form' => [
            'methods' => [
                'label' => 'Načini dostave',
                'add_label' => 'Dodaj pravilo',
            ],
            'shipping_method_id' => [
                'label' => 'Način dostave',
                'placeholder' => 'Bilo koji način dostave',
            ],
            'type' => [
                'label' => 'Vrsta popusta',
                'options' => [
                    'fixed' => 'Fiksna cijena',
                    'percentage' => 'Postotak popusta',
                ],
            ],
            'percentage' => [
                'label' => 'Postotak popusta (%)',
            ],
        ],
    ],
];
