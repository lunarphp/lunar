<?php

return [
    'plural_label' => 'Discounts',
    'label' => 'Discount',
    'form' => [
        'conditions' => [
            'heading' => 'Conditions',
        ],
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
        'coupon' => [
            'label' => 'Coupon',
            'helper_text' => 'Enter the coupon required for the discount to apply, if left blank it will apply automatically.',
        ],
        'max_uses' => [
            'label' => 'Max uses',
            'helper_text' => 'Leave blank for unlimited uses.',
        ],
        'max_uses_per_user' => [
            'label' => 'Max uses per user',
            'helper_text' => 'Leave blank for unlimited uses.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Minimum Cart Amount',
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
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Option(s)',
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
