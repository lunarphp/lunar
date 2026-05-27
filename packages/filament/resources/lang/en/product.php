<?php

return [

    'label' => 'Product',

    'plural_label' => 'Products',

    'tabs' => [
        'all' => 'All',
        'published' => 'Published',
        'draft' => 'Draft',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Currently in draft status, this product is unavailable across all channels and customer groups.',
        ],
        'availability' => [
            'customer_groups' => 'This product is currently unavailable for all customer groups.',
            'channels' => 'This product is currently unavailable for all channels.',
            'hidden_from_guests' => 'Guests cannot currently see or buy this product. The default customer group is not enabled or visible for it.',
            'no_default_customer_group' => 'No default customer group is set, so guest visibility cannot be controlled here. Mark one customer group as default to gate guest access.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
                'archived' => 'Archived',
                'deleted' => 'Deleted',
                'draft' => 'Draft',
                'published' => 'Published',
            ],
        ],
        'name' => [
            'label' => 'Name',
        ],
        'brand' => [
            'label' => 'Brand',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Stock',
        ],
        'producttype' => [
            'label' => 'Product Type',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Update Status',
            'heading' => 'Update Status',
        ],
    ],

    'form' => [

        'description' => [
            'label' => 'Description',
        ],

        'short_description' => [
            'label' => 'Short Description',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'brand' => [
            'label' => 'Brand',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Product Type',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Published',
                    'description' => 'This product will be available across all enabled customer groups and channels',
                ],
                'draft' => [
                    'label' => 'Draft',
                    'description' => 'This product will be hidden across all channels and customer groups',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tags',
            'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
        ],
        'collections' => [
            'label' => 'Collections',
            'select_collection' => 'Select a collection',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Availability',
        ],
        'edit' => [
            'title' => 'Basic Information',
        ],
        'identifiers' => [
            'label' => 'Product Identifiers',
        ],
        'inventory' => [
            'label' => 'Inventory',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Tax Class',
                ],
                'tax_ref' => [
                    'label' => 'Tax Reference',
                    'helper_text' => 'Optional, for integration with 3rd party systems.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Shipping',
        ],
        'variants' => [
            'label' => 'Variants',
        ],
        'collections' => [
            'label' => 'Collections',
            'select_collection' => 'Select a collection',
        ],
        'associations' => [
            'label' => 'Product Associations',
        ],
    ],

];
