<?php

return [
    'shipping_discount' => [
        'name' => 'Shipping Price',
        'form' => [
            'methods' => [
                'label' => 'Shipping Methods',
                'add_label' => 'Add Rule',
            ],
            'shipping_method_id' => [
                'label' => 'Shipping Method',
                'placeholder' => 'Any shipping method',
            ],
            'type' => [
                'label' => 'Discount Type',
                'options' => [
                    'fixed' => 'Fixed Price',
                    'percentage' => 'Percentage Off',
                ],
            ],
            'percentage' => [
                'label' => 'Percentage Off (%)',
            ],
        ],
    ],
];
