<?php

return [

    'order_update' => [
        'label' => 'Order update',
        'subject' => 'An update on your order :reference',
        'greeting' => 'Hello,',
        'intro' => 'We wanted to give you an update on your order :reference.',
        'outro' => 'Thank you for shopping with us.',
    ],

    'order_confirmation' => [
        'label' => 'Order confirmation',
        'subject' => 'Your order :reference has been received',
        'heading' => 'Thank you for your order',
        'intro' => 'We have received your order :reference, placed on :date. Here is a summary of what you ordered.',
        'outro' => 'We will let you know as soon as your order is on its way.',
    ],

    'payment_received' => [
        'label' => 'Payment received',
        'subject' => 'Payment received for order :reference',
        'heading' => 'Payment received',
        'intro' => 'We have received your payment for order :reference.',
        'outro' => 'Thank you. We will be in touch when your order is on its way.',
    ],

    'order_shipped' => [
        'label' => 'Order shipped',
        'subject' => 'Your order :reference is on its way',
        'heading' => 'Your order is on its way',
        'intro' => 'Good news: the following items from your order :reference have been shipped.',
        'tracking' => 'You can follow your delivery using the tracking details below.',
        'outro' => 'Thank you for shopping with us.',
    ],

    'order_ready_for_collection' => [
        'label' => 'Ready for collection',
        'subject' => 'Your order :reference is ready to collect',
        'heading' => 'Your order is ready to collect',
        'intro' => 'The following items from your order :reference are ready for you to collect.',
        'outro' => 'Please bring your order reference with you when you collect.',
    ],

    'order_provisioned' => [
        'label' => 'Order provisioned',
        'subject' => 'Your order :reference is ready',
        'heading' => 'Your digital items are ready',
        'intro' => 'The following items from your order :reference are now available.',
        'outro' => 'Thank you for shopping with us.',
    ],

    'return_received' => [
        'label' => 'Return received',
        'subject' => 'We have received your return for order :reference',
        'heading' => 'Return received',
        'intro' => 'We have received the following items back from your order :reference.',
        'outro' => 'If a refund is due, we will confirm it in a separate email.',
    ],

    'order_cancelled' => [
        'label' => 'Order cancelled',
        'subject' => 'Your order :reference has been cancelled',
        'heading' => 'Your order has been cancelled',
        'intro' => 'Your order :reference has been cancelled.',
        'outro' => 'If you have any questions, please reply to this email.',
    ],

    'refund_issued' => [
        'label' => 'Refund issued',
        'subject' => 'A refund has been issued for order :reference',
        'heading' => 'Refund issued',
        'intro' => 'We have issued a refund for your order :reference.',
        'outro' => 'Depending on your payment provider, it can take a few days for the refund to appear.',
    ],

    'partial_fulfilment_update' => [
        'label' => 'Partial fulfilment update',
        'subject' => 'An update on the rest of your order :reference',
        'heading' => 'An update on your order',
        'intro' => 'Part of your order :reference has been dispatched. The remaining items are still being prepared.',
        'outstanding' => 'The following items are still to come:',
        'outro' => 'We are sorry for the delay and will let you know as soon as they are on their way.',
    ],

    'partials' => [
        'greeting' => 'Hi :name,',
        'greeting_fallback' => 'Hello,',
        'signoff' => 'Regards,',
        'item' => 'Item',
        'quantity' => 'Quantity',
        'total' => 'Total',
        'sub_total' => 'Sub total',
        'discount' => 'Discount',
        'shipping' => 'Shipping',
        'tax' => 'Tax',
        'order_total' => 'Order total',
        'carrier' => 'Carrier',
        'tracking_number' => 'Tracking number',
        'track' => 'Track your parcel',
        'shipping_address' => 'Shipping address',
        'billing_address' => 'Billing address',
        'payment_status' => 'Payment status',
        'collect_from' => 'Collect from',
        'reason' => 'Reason',
        'refund_amount' => 'Refund amount',
    ],

];
