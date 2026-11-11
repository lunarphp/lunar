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
        'panel' => [
            'rules' => 'Rules',
            'rules_description' => 'Each rule applies to one shipping method, or to every method when none is chosen. A specific method wins over the catch-all.',
            'add_rule' => 'Add rule',
            'remove_rule' => 'Remove rule',
            'no_rules' => 'No rules yet. Add one to change what shipping costs.',
            'method' => 'Shipping method',
            'method_any' => 'Any shipping method',
            'type' => 'Effect',
            'type_fixed' => 'Set the shipping price',
            'type_percentage' => 'Take a percentage off',
            'percentage' => 'Percentage off (%)',
            'prices' => 'Shipping price becomes',
            'prices_hint' => 'The customer pays this for shipping. Leave a currency blank to leave its price unchanged; enter 0 for free shipping.',
        ],
        'summary_free' => 'Free shipping',
        'summary_percentage' => ':percentage% off shipping',
        'summary_from' => 'Shipping from :amount',
    ],
];
