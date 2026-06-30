<?php

return [
    'public_id' => [
        'label' => 'Public ID',
    ],

    'tags' => [
        'notification' => [

            'updated' => 'Tags updated',

        ],
    ],

    'activity-log' => [

        'input' => [

            'placeholder' => 'Add a comment',

        ],

        'action' => [

            'add-comment' => 'Add Comment',

        ],

        'system' => 'System',

        'partials' => [
            'orders' => [
                'order_created' => 'Order Created',

                'status_change' => 'Status updated',

                'order_closed' => 'Order closed',

                'order_reopened' => 'Order reopened',

                'order_cancelled' => 'Order cancelled (:reason)',

                'order_cancelled_no_reason' => 'Order cancelled',

                'email_notification' => 'Sent :notification to :email',

                'email_notification_fallback' => 'a notification',

                'fulfilment_state' => 'Fulfilment #:id marked as :state',

                'fulfilment_held' => 'Fulfilment #:id placed on hold (:reason)',

                'fulfilment_held_no_reason' => 'Fulfilment #:id placed on hold',

                'fulfilment_released' => 'Fulfilment #:id released from hold',

                'capture' => 'Payment of :amount on card ending :last_four',

                'authorized' => 'Authorized of :amount on card ending :last_four',

                'refund' => 'Refund of :amount on card ending :last_four',

                'address' => ':type updated',

                'billingAddress' => 'Billing address',

                'shippingAddress' => 'Shipping address',
            ],

            'update' => [
                'updated' => ':model updated',
            ],

            'create' => [
                'created' => ':model created',
            ],

            'tags' => [
                'updated' => 'Tags updated',
                'added' => 'Added',
                'removed' => 'Removed',
            ],
        ],

        'notification' => [
            'comment_added' => 'Comment added',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Enter the ID of the YouTube video. e.g. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Parent Collection',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Collections Reordered',
            ],
            'node-expanded' => [
                'danger' => 'Unable to load collections',
            ],
            'delete' => [
                'danger' => 'Unable to delete collection',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Add Option',
        ],
        'delete-option' => [
            'label' => 'Delete Option',
        ],
        'remove-shared-option' => [
            'label' => 'Remove Shared Option',
        ],
        'add-value' => [
            'label' => 'Add Another Value',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'values' => [
            'label' => 'Values',
        ],
    ],
];
