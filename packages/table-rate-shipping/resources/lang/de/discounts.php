<?php

return [
    'shipping_discount' => [
        'name' => 'Versandpreis',
        'form' => [
            'methods' => [
                'label' => 'Versandarten',
                'add_label' => 'Regel hinzufügen',
            ],
            'shipping_method_id' => [
                'label' => 'Versandart',
                'placeholder' => 'Beliebige Versandart',
            ],
            'type' => [
                'label' => 'Rabatttyp',
                'options' => [
                    'fixed' => 'Festpreis',
                    'percentage' => 'Prozentualer Rabatt',
                ],
            ],
            'percentage' => [
                'label' => 'Prozentualer Rabatt (%)',
            ],
        ],
    ],
];
