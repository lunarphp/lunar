<?php

return [
    'tags' => [

        'input' => [

            'placeholder' => 'Seperate tags with ,',

        ],

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
];
