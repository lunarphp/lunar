<?php

return [
    'plural_label' => 'Discounts',
    'label' => 'Discount',
    'form' => [
        'conditions' => [
            'heading' => 'Conditions',
        ],
        'buy_x_get_y' => [
            'heading' => 'Buy X Get Y',
        ],
        'amount_off' => [
            'heading' => 'Amount Off',
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
                    'label' => 'Medium',
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
        'min_qty' => [
            'label' => 'Product Quantity',
            'helper_text' => 'Set how many qualifying products are required for the discount to apply.',
        ],
        'reward_qty' => [
            'label' => 'No. of free items',
            'helper_text' => 'How many of each item are discounted.',
        ],
        'max_reward_qty' => [
            'label' => 'Maximum reward quantity',
            'helper_text' => 'The maximum amount of products which can be discounted, regardless of criteria.',
        ],
        'automatic_rewards' => [
            'label' => 'Automatically add rewards',
            'helper_text' => 'Switch on to add reward products when not present in the basket.',
        ],
        'fixed_value' => [
            'label' => 'Fixed value',
        ],
        'percentage' => [
            'label' => 'Percentage',
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
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Availability',
        ],
        'edit' => [
            'title' => 'Basic Information',
        ],
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
        'customers' => [
            'title' => 'Customers',
            'description' => 'Select which customers this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Customer',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
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
        'rewards' => [
            'title' => 'Rewards',
            'description' => 'Select which products will be discounted if they exist in the cart and the above conditions are met.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Reward',
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
        'conditions' => [
            'title' => 'Product and Variant Conditions',
            'description' => 'Select the product or variant conditions required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Condition',
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
        'collection_conditions' => [
            'title' => 'Collection Conditions',
            'description' => 'Select the collection conditions required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Condition',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
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
