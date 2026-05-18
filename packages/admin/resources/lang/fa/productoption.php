<?php

return [

    'label' => 'Product Option',

    'plural_label' => 'Product Options',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'label' => [
            'label' => 'Label',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'shared' => [
            'label' => 'Shared',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'label' => [
            'label' => 'Label',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Product Variants Saved',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Cancel',
                ],
                'save-options' => [
                    'label' => 'Save Options',
                ],
                'add-shared-option' => [
                    'label' => 'Add Shared Option',
                    'form' => [
                        'product_option' => [
                            'label' => 'Product Option',
                        ],
                        'no_shared_components' => [
                            'label' => 'No shared options are available.',
                        ],
                        'preselect' => [
                            'label' => 'Preselect all values by default.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Add Option',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'There are no product options configured',
                    'description' => 'Add a shared or restricted product option to start generating some variants.',
                ],
            ],
            'options-table' => [
                'title' => 'Product Options',
                'configure-options' => [
                    'label' => 'Configure Options',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Option',
                    ],
                    'values' => [
                        'label' => 'Values',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Product Variants',
                'actions' => [
                    'create' => [
                        'label' => 'Create Variant',
                    ],
                    'edit' => [
                        'label' => 'Edit',
                    ],
                    'delete' => [
                        'label' => 'Delete',
                    ],
                ],
                'empty' => [
                    'heading' => 'No Variants Configured',
                ],
                'table' => [
                    'new' => [
                        'label' => 'NEW',
                    ],
                    'option' => [
                        'label' => 'Option',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Price',
                    ],
                    'stock' => [
                        'label' => 'Stock',
                    ],
                ],
            ],
        ],
    ],

];
