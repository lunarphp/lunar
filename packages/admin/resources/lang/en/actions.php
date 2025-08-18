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
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Cannot Delete',
                    'body' => 'This collection has child collections and cannot be deleted.',
                ],
            ],
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
                    'no_mailers' => 'There are no mailers available for this status.',
                ],
                'step_three' => [
                    'label' => 'Preview & Save',
                    'no_mailers' => 'No mailers have been chosen for preview.',
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
