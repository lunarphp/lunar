<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Create Root Collection',
        ],
        'create_child' => [
            'label' => 'Create Child Collection',
        ],
        'move' => [
            'label' => 'Move Collection',
        ],
        'delete' => [
            'label' => 'Delete',
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Update Status',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'Mailers & Notifications',
                ],
                'step_three' => [
                    'label' => 'Preview & Save',
                ],
            ],
            'notification' => [
                'label' => 'Order status updated',
            ],
            'billing_email' => [
                'label' => 'Billing Email',
            ],
            'shipping_email' => [
                'label' => 'Shipping Email',
            ],
        ],

    ],
];
