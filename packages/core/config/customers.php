<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Customer Deletion Strategy
    |--------------------------------------------------------------------------
    |
    | This option controls the behavior when deleting a customer. You can choose
    | between 'anonymize' (recommended for GDPR compliance) or 'delete' (hard delete).
    | When using 'anonymize', customer data is anonymized but records are preserved
    | for data integrity. When using 'delete', customer data is permanently removed.
    |
    | Supported: "anonymize", "delete"
    |
    */
    'deletion_strategy' => env('LUNAR_CUSTOMER_DELETION_STRATEGY', 'anonymize'),

    /*
    |--------------------------------------------------------------------------
    | Anonymization Fields
    |--------------------------------------------------------------------------
    |
    | Define the fields and their replacement values when anonymizing customer data.
    | Use :id as a placeholder for the customer ID in replacement values.
    |
    */
    'anonymization_fields' => [
        'first_name' => 'Anonymous',
        'last_name' => 'Customer',
        'company_name' => null,
        'tax_identifier' => null,
        'account_ref' => null,
        'meta' => [],
        'attribute_data' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preserve Order Data
    |--------------------------------------------------------------------------
    |
    | When true, order data will be preserved even when deleting a customer.
    | This is often required for legal and accounting purposes. Orders will
    | be anonymized but not deleted.
    |
    */
    'preserve_order_data' => true,

    /*
    |--------------------------------------------------------------------------
    | Delete Orphaned Users
    |--------------------------------------------------------------------------
    |
    | When true, if a user is only associated with the customer being deleted,
    | the user account will also be deleted to free up the email address.
    |
    */
    'delete_orphaned_users' => true,

    /*
    |--------------------------------------------------------------------------
    | Handle Cart Data
    |--------------------------------------------------------------------------
    |
    | Define how to handle cart data when deleting a customer.
    | Options: "delete" (remove carts), "anonymize" (keep but anonymize)
    |
    */
    'cart_handling' => 'delete',
];