<?php

use Lunar\Models\Discount;

return [
    'plural_label' => 'الخصومات',
    'label' => 'خصم',
    'form' => [
        'conditions' => [
            'heading' => 'الشروط',
        ],
        'buy_x_get_y' => [
            'heading' => 'اشترِ X واحصل على Y',
        ],
        'amount_off' => [
            'heading' => 'مقدار الخصم',
        ],
        'name' => [
            'label' => 'الاسم',
        ],
        'handle' => [
            'label' => 'المعرف',
        ],
        'starts_at' => [
            'label' => 'تاريخ البداية',
        ],
        'ends_at' => [
            'label' => 'تاريخ الانتهاء',
        ],
        'priority' => [
            'label' => 'الأولوية',
            'helper_text' => 'سيتم تطبيق الخصومات ذات الأولوية الأعلى أولاً.',
            'options' => [
                'low' => [
                    'label' => 'منخفضة',
                ],
                'medium' => [
                    'label' => 'متوسطة',
                ],
                'high' => [
                    'label' => 'مرتفعة',
                ],
            ],
        ],
        'stop' => [
            'label' => 'إيقاف تطبيق الخصومات الأخرى بعد هذا الخصم',
        ],
        'coupon' => [
            'label' => 'كوبون',
            'helper_text' => 'أدخل الكوبون المطلوب لتطبيق الخصم، إذا تُرك فارغًا سيتم التطبيق تلقائيًا.',
        ],
        'max_uses' => [
            'label' => 'الحد الأقصى للاستخدام',
            'helper_text' => 'اتركه فارغًا لعدد استخدامات غير محدود.',
        ],
        'max_uses_per_user' => [
            'label' => 'الحد الأقصى للاستخدام لكل مستخدم',
            'helper_text' => 'اتركه فارغًا لعدد استخدامات غير محدود.',
        ],
        'minimum_cart_amount' => [
            'label' => 'الحد الأدنى لمبلغ السلة',
        ],
        'min_qty' => [
            'label' => 'كمية المنتج',
            'helper_text' => 'حدد عدد المنتجات المطلوبة لتطبيق الخصم.',
        ],
        'reward_qty' => [
            'label' => 'عدد العناصر المجانية',
            'helper_text' => 'عدد كل عنصر يتم تطبيق الخصم عليه.',
        ],
        'max_reward_qty' => [
            'label' => 'الحد الأقصى لعدد المكافآت',
            'helper_text' => 'الحد الأقصى للمنتجات التي يمكن تطبيق الخصم عليها بغض النظر عن الشروط.',
        ],
        'automatic_rewards' => [
            'label' => 'إضافة المكافآت تلقائيًا',
            'helper_text' => 'حددها لإضافة منتجات المكافأة تلقائيًا إذا لم تكن موجودة في السلة.',
        ],
        'fixed_value' => [
            'label' => 'قيمة ثابتة',
        ],
        'percentage' => [
            'label' => 'النسبة المئوية',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'الاسم',
        ],
        'status' => [
            'label' => 'الحالة',
            Discount::ACTIVE => [
                'label' => 'نشط',
            ],
            Discount::PENDING => [
                'label' => 'قيد الانتظار',
            ],
            Discount::EXPIRED => [
                'label' => 'منتهي',
            ],
            Discount::SCHEDULED => [
                'label' => 'مجدول',
            ],
        ],
        'type' => [
            'label' => 'النوع',
        ],
        'starts_at' => [
            'label' => 'تاريخ البداية',
        ],
        'ends_at' => [
            'label' => 'تاريخ الانتهاء',
        ],
        'created_at' => [
            'label' => 'تاريخ الإنشاء',
        ],
        'coupon' => [
            'label' => 'كوبون',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'التوافر',
        ],
        'edit' => [
            'title' => 'المعلومات الأساسية',
        ],
        'limitations' => [
            'label' => 'القيود',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'المجموعات',
            'description' => 'حدد المجموعات التي يقتصر عليها هذا الخصم.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة مجموعة',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'type' => [
                    'label' => 'النوع',
                    'limitation' => [
                        'label' => 'تقييد',
                    ],
                    'exclusion' => [
                        'label' => 'استبعاد',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'تقييد',
                        ],
                        'exclusion' => [
                            'label' => 'استبعاد',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'العملاء',
            'description' => 'حدد العملاء الذين يقتصر عليهم هذا الخصم.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة عميل',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
            ],
        ],
        'brands' => [
            'title' => 'العلامات التجارية',
            'description' => 'حدد العلامات التجارية التي يقتصر عليها هذا الخصم.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة علامة تجارية',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'type' => [
                    'label' => 'النوع',
                    'limitation' => [
                        'label' => 'تقييد',
                    ],
                    'exclusion' => [
                        'label' => 'استبعاد',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'تقييد',
                        ],
                        'exclusion' => [
                            'label' => 'استبعاد',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'المنتجات',
            'description' => 'حدد المنتجات التي يقتصر عليها هذا الخصم.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة منتج',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'type' => [
                    'label' => 'النوع',
                    'limitation' => [
                        'label' => 'تقييد',
                    ],
                    'exclusion' => [
                        'label' => 'استبعاد',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'تقييد',
                        ],
                        'exclusion' => [
                            'label' => 'استبعاد',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'المكافآت',
            'description' => 'حدد المنتجات التي سيتم تطبيق الخصم عليها إذا كانت موجودة في السلة وتم استيفاء الشروط أعلاه.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة مكافأة',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'type' => [
                    'label' => 'النوع',
                    'limitation' => [
                        'label' => 'تقييد',
                    ],
                    'exclusion' => [
                        'label' => 'استبعاد',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'تقييد',
                        ],
                        'exclusion' => [
                            'label' => 'استبعاد',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'شروط المنتجات والمتغيرات',
            'description' => 'حدد الشروط المطلوبة لتطبيق الخصم على المنتجات أو المتغيرات.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة شرط',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'type' => [
                    'label' => 'النوع',
                    'limitation' => [
                        'label' => 'تقييد',
                    ],
                    'exclusion' => [
                        'label' => 'استبعاد',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'تقييد',
                        ],
                        'exclusion' => [
                            'label' => 'استبعاد',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'شروط المجموعات',
            'description' => 'حدد شروط المجموعات المطلوبة لتطبيق الخصم.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة شرط',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'متغيرات المنتجات',
            'description' => 'حدد متغيرات المنتجات التي يقتصر عليها هذا الخصم.',
            'actions' => [
                'attach' => [
                    'label' => 'إضافة متغير منتج',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'الاسم',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'الخيار / الخيارات',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'تقييد',
                        ],
                        'exclusion' => [
                            'label' => 'استبعاد',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
