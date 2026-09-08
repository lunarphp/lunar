<?php

return [
    'shipping_discount' => [
        'name' => 'Verzendprijs',
        'form' => [
            'methods' => [
                'label' => 'Verzendmethoden',
                'add_label' => 'Regel toevoegen',
            ],
            'shipping_method_id' => [
                'label' => 'Verzendmethode',
                'placeholder' => 'Elke verzendmethode',
            ],
            'type' => [
                'label' => 'Kortingstype',
                'options' => [
                    'fixed' => 'Vaste prijs',
                    'percentage' => 'Procentuele korting',
                ],
            ],
            'percentage' => [
                'label' => 'Procentuele korting (%)',
            ],
        ],
    ],
];
