<?php

return [
    'plural_label' => 'Discounts',
    'label' => 'Discount',
    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'starts_at' => [
            'label' => 'Start Date',
        ],
        'ends_at' => [
            'label' => 'End Date',
        ],
        'priority' => [
            'label' => 'Priority',
            'helper_text' => 'Discounts with higher priority will be applied first.',
            'options' => [
                'low' => [
                    'label' => 'Low',
                ],
                'medium' => [
                    'label' => 'Low',
                ],
                'high' => [
                    'label' => 'High',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Stop other discounts applying after this one',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'status' => [
            'label' => 'Status',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Active',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'Pending',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Expired',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Scheduled',
            ],
        ],
        'type' => [
            'label' => 'Type',
        ],
        'starts_at' => [
            'label' => 'Start Date',
        ],
        'ends_at' => [
            'label' => 'End Date',
        ],
    ],
    'pages' => [
        'limitations' => [
            'label' => 'Limitations',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Collections',
            'description' => 'Select which collections this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Collection',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'brands' => [
            'title' => 'Brands',
            'description' => 'Select which brands this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Brand',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Products',
            'description' => 'Select which products this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Product',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Product Variants',
            'description' => 'Select which product variants this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Product Variant',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Limitation',
                    ],
                    'exclusion' => [
                        'label' => 'Exclusion',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitation',
                        ],
                        'exclusion' => [
                            'label' => 'Exclusion',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
