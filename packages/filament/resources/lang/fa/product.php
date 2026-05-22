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
            'content' => 'در حال حاضر در وضعیت پیش‌نویس، این محصول در همه کانال‌ها و گروه‌های مشتری در دسترس نیست.',
        ],
        'availability' => [
            'customer_groups' => 'این محصول در حال حاضر برای همه گروه‌های مشتری در دسترس نیست.',
            'channels' => 'این محصول در حال حاضر برای همه کانال‌ها در دسترس نیست.',
            'hidden_from_guests' => 'مهمانان در حال حاضر نمی‌توانند این محصول را ببینند یا بخرند. گروه مشتری پیش‌فرض برای آن فعال یا قابل مشاهده نیست.',
            'no_default_customer_group' => 'گروه مشتری پیش‌فرضی تنظیم نشده است، بنابراین مشاهده‌پذیری مهمانان از اینجا قابل کنترل نیست. یک گروه مشتری را به‌عنوان پیش‌فرض علامت بزنید تا دسترسی مهمانان را مدیریت کنید.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
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
