<?php

return [

    'label' => 'Order',

    'plural_label' => 'Orders',

    'breadcrumb' => [
        'manage' => 'Manage',
    ],

    'tabs' => [
        'all' => 'All',
    ],

    'transactions' => [
        'capture' => 'Captured',
        'intent' => 'Intent',
        'refund' => 'Refunded',
        'failed' => 'Failed',
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Reference',
        ],
        'customer_reference' => [
            'label' => 'Customer Reference',
        ],
        'customer' => [
            'label' => 'Customer',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'postcode' => [
            'label' => 'Postcode',
        ],
        'email' => [
            'label' => 'Email',
            'copy_message' => 'Email address copied',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'date' => [
            'label' => 'Date',
        ],
        'new_customer' => [
            'label' => 'Customer Type',
        ],
        'placed_after' => [
            'label' => 'Placed after',
        ],
        'placed_before' => [
            'label' => 'Placed before',
        ],
    ],

    'form' => [
        'address' => [
            'first_name' => [
                'label' => 'First Name',
            ],
            'last_name' => [
                'label' => 'Last Name',
            ],
            'line_one' => [
                'label' => 'Address Line 1',
            ],
            'line_two' => [
                'label' => 'Address Line 2',
            ],
            'line_three' => [
                'label' => 'Address Line 3',
            ],
            'company_name' => [
                'label' => 'Company Name',
            ],
            'tax_identifier' => [
                'label' => 'Tax Identifier',
            ],
            'contact_phone' => [
                'label' => 'Phone',
            ],
            'contact_email' => [
                'label' => 'Email Address',
            ],
            'city' => [
                'label' => 'City',
            ],
            'state' => [
                'label' => 'State / Province',
            ],
            'postcode' => [
                'label' => 'Postal Code',
            ],
            'country_id' => [
                'label' => 'Country',
            ],
        ],

        'reference' => [
            'label' => 'Reference',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'transaction' => [
            'label' => 'Transaction',
        ],
        'amount' => [
            'label' => 'Amount',

            'hint' => [
                'less_than_total' => "You're about to capture an amount less than the total transaction value",
            ],
        ],

        'notes' => [
            'label' => 'Notes',
        ],
        'confirm' => [
            'label' => 'Confirm',

            'alert' => 'Confirmation required',

            'hint' => [
                'capture' => 'Please confirm you want to capture this payment',
                'refund' => 'Please confirm you wish to refund this amount.',
            ],
        ],
    ],

    'infolist' => [
        'notes' => [
            'label' => 'Notes',
            'placeholder' => 'No notes on this order',
        ],
        'delivery_instructions' => [
            'label' => 'Delivery Instructions',
        ],
        'shipping_total' => [
            'label' => 'Shipping Total',
        ],
        'paid' => [
            'label' => 'Paid',
        ],
        'refund' => [
            'label' => 'Refund',
        ],
        'unit_price' => [
            'label' => 'Unit Price',
        ],
        'quantity' => [
            'label' => 'Quantity',
        ],
        'sub_total' => [
            'label' => 'Sub Total',
        ],
        'discount_total' => [
            'label' => 'Discount Total',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'current_stock_level' => [
            'message' => 'Current Stock Level: :count',
        ],
        'purchase_stock_level' => [
            'message' => 'at time of ordering: :count',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'reference' => [
            'label' => 'Reference',
        ],
        'customer_reference' => [
            'label' => 'Customer Reference',
        ],
        'channel' => [
            'label' => 'Channel',
        ],
        'date_created' => [
            'label' => 'Date Created',
        ],
        'date_placed' => [
            'label' => 'Date Placed',
        ],
        'new_returning' => [
            'label' => 'New / Returning',
        ],
        'new_customer' => [
            'label' => 'New Customer',
        ],
        'returning_customer' => [
            'label' => 'Returning Customer',
        ],
        'shipping_address' => [
            'label' => 'Shipping Address',
        ],
        'billing_address' => [
            'label' => 'Billing Address',
        ],
        'address_not_set' => [
            'label' => 'No address set',
        ],
        'billing_matches_shipping' => [
            'label' => 'Same as shipping address',
        ],
        'additional_info' => [
            'label' => 'Additional Information',
        ],
        'no_additional_info' => [
            'label' => 'No Additional Information',
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'timeline' => [
            'label' => 'Timeline',
        ],
        'transactions' => [
            'label' => 'Transactions',
            'placeholder' => 'No transactions',
        ],
        'alert' => [
            'requires_capture' => 'This order still requires payment to be captured.',
            'partially_refunded' => 'This order has been partially refunded.',
            'refunded' => 'This order has been refunded.',
        ],
    ],

    'action' => [
        'bulk_update_status' => [
            'label' => 'Update Status',
            'notification' => 'Orders status updated',
        ],
        'update_status' => [
            'new_status' => [
                'label' => 'New status',
            ],
            'additional_content' => [
                'label' => 'Additional content',
            ],
            'additional_email_recipient' => [
                'label' => 'Additional email recipient',
                'placeholder' => 'optional',
            ],
        ],
        'download_order_pdf' => [
            'label' => 'Download PDF',
            'notification' => 'Order PDF downloading',
        ],
        'edit_address' => [
            'label' => 'Edit',

            'notification' => [
                'error' => 'Error',

                'billing_address' => [
                    'saved' => 'Billing address saved',
                ],

                'shipping_address' => [
                    'saved' => 'Shipping address saved',
                ],
            ],
        ],
        'edit_tags' => [
            'label' => 'Edit',
            'form' => [
                'tags' => [
                    'label' => 'Tags',
                    'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
                ],
            ],
        ],
        'capture_payment' => [
            'label' => 'Capture Payment',

            'notification' => [
                'error' => 'There was a problem with the capture',
                'success' => 'Capture successful',
            ],
        ],
        'refund_payment' => [
            'label' => 'Refund',

            'notification' => [
                'error' => 'There was a problem with the refund',
                'success' => 'Refund successful',
            ],
        ],
    ],

];
