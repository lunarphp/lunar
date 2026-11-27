<?php

return [
    'inventory' => [
        'summary_heading' => 'Stock',
        'location' => 'Location: :location',
        'on_hand' => 'On hand',
        'available' => 'Available',
        'committed' => 'Committed',
        'reserved' => 'Reserved',
        'recent_movements' => 'Recent movements',
        'no_movements' => 'No stock movements yet.',
    ],
    'label' => 'Product Variant',
    'plural_label' => 'Product Variants',
    'pages' => [
        'edit' => [
            'title' => 'Basic Information',
        ],
        'media' => [
            'title' => 'Media',
            'form' => [
                'no_selection' => [
                    'label' => 'You do not currently have an image selected for this variant.',
                ],
                'no_media_available' => [
                    'label' => 'There is currently no media available on this product.',
                ],
                'images' => [
                    'label' => 'Primary Image',
                    'helper_text' => 'Select the product image which represents this variant.',
                ],
            ],
        ],
        'identifiers' => [
            'title' => 'Identifiers',
        ],
        'inventory' => [
            'title' => 'Inventory',
        ],
        'shipping' => [
            'title' => 'Shipping',
        ],
    ],
    'form' => [
        'sku' => [
            'label' => 'SKU',
        ],
        'gtin' => [
            'label' => 'Global Trade Item Number (GTIN)',
        ],
        'mpn' => [
            'label' => 'Manufacturer Part Number (MPN)',
        ],
        'ean' => [
            'label' => 'UPC/EAN',
        ],
        'continue_selling' => [
            'label' => 'ادامه فروش در صورت اتمام موجودی',
            'tooltip' => 'در صورت فعال بودن، این گونه بدون توجه به موجودی قابل سفارش است. در صورت غیرفعال بودن، مشتریان فقط می‌توانند کالای موجود را بخرند.',
        ],
        'sell_against_incoming' => [
            'label' => 'فروش در برابر موجودی ورودی',
            'tooltip' => 'موجودی ورودی به‌عنوان موجود محاسبه می‌شود تا مشتریان بتوانند واحدهای مورد انتظار را سفارش دهند.',
        ],
        'unit_quantity' => [
            'label' => 'Unit Quantity',
            'tooltip' => 'How many individual items make up 1 unit.',
        ],
        'min_quantity' => [
            'label' => 'Minimum Quantity',
            'tooltip' => 'The minimum quantity of a product variant that can be bought in a single purchase.',
        ],
        'quantity_increment' => [
            'label' => 'Quantity Increment',
            'tooltip' => 'The product variant must be purchased in multiples of this quantity.',
        ],
        'tax_class_id' => [
            'label' => 'Tax Class',
        ],
        'shippable' => [
            'label' => 'Shippable',
        ],
        'fulfilment_methods' => [
            'label' => 'روش‌های تأمین سفارش',
            'helper_text' => 'این گونه چگونه تأمین می‌شود. تیک زدن یک روش فیزیکی مانند ارسال یا تحویل حضوری آن را قابل ارسال می‌کند. اگر چیزی برای تأمین وجود ندارد، مانند دانلود فوری، هیچ روشی را تیک نزنید.',
            'unavailable' => ':methods در حال حاضر در دسترس نیست، بنابراین مشتریان نمی‌توانند آن را انتخاب کنند. تیک آن را بردارید یا دوباره آن را در دسترس قرار دهید.',
        ],
        'length_value' => [
            'label' => 'Length',
        ],
        'length_unit' => [
            'label' => 'Length Unit',
        ],
        'width_value' => [
            'label' => 'Width',
        ],
        'width_unit' => [
            'label' => 'Width Unit',
        ],
        'height_value' => [
            'label' => 'Height',
        ],
        'height_unit' => [
            'label' => 'Height Unit',
        ],
        'weight_value' => [
            'label' => 'Weight',
        ],
        'weight_unit' => [
            'label' => 'Weight Unit',
        ],
    ],
];
