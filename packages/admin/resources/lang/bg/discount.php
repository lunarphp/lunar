<?php

use Lunar\Models\Discount;

return [
    'plural_label' => 'Отстъпки',
    'label' => 'Отстъпка',
    'form' => [
        'conditions' => [
            'heading' => 'Условия',
        ],
        'buy_x_get_y' => [
            'heading' => 'Купи X Вземи Y',
        ],
        'amount_off' => [
            'heading' => 'Отстъпка от сума',
        ],
        'name' => [
            'label' => 'Име',
        ],
        'handle' => [
            'label' => 'Код',
        ],
        'starts_at' => [
            'label' => 'Начална дата',
        ],
        'ends_at' => [
            'label' => 'Крайна дата',
        ],
        'priority' => [
            'label' => 'Приоритет',
            'helper_text' => 'Отстъпките с по-висок приоритет ще бъдат приложени първи.',
            'options' => [
                'low' => [
                    'label' => 'Нисък',
                ],
                'medium' => [
                    'label' => 'Среден',
                ],
                'high' => [
                    'label' => 'Висок',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Спиране на прилагането на други отстъпки след тази',
        ],
        'coupon' => [
            'label' => 'Купон',
            'helper_text' => 'Въведете купон за прилагане на отстъпката. Ако е празно, отстъпката се прилага автоматично.',
        ],
        'max_uses' => [
            'label' => 'Максимален брой използвания',
            'helper_text' => 'Оставете празно за неограничен брой използвания.',
        ],
        'max_uses_per_user' => [
            'label' => 'Максимален брой използвания на потребител',
            'helper_text' => 'Оставете празно за неограничен брой използвания.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Минимална стойност на количката',
        ],
        'min_qty' => [
            'label' => 'Количество продукти',
            'helper_text' => 'Задайте колко продукти са необходими за прилагане на отстъпката.',
        ],
        'reward_qty' => [
            'label' => 'Брой безплатни артикули',
            'helper_text' => 'Колко от всеки артикул са с отстъпка.',
        ],
        'max_reward_qty' => [
            'label' => 'Максимално количество награди',
            'helper_text' => 'Максималният брой продукти, които могат да бъдат намалени, независимо от условията.',
        ],
        'automatic_rewards' => [
            'label' => 'Автоматично добавяне на награди',
            'helper_text' => 'Включете, за да се добавят автоматично наградни продукти, ако липсват в количката.',
        ],
        'fixed_value' => [
            'label' => 'Фиксирана стойност',
        ],
        'percentage' => [
            'label' => 'Процент',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Име',
        ],
        'status' => [
            'label' => 'Статус',
            Discount::ACTIVE => [
                'label' => 'Активна',
            ],
            Discount::PENDING => [
                'label' => 'Очакваща',
            ],
            Discount::EXPIRED => [
                'label' => 'Изтекла',
            ],
            Discount::SCHEDULED => [
                'label' => 'Планирана',
            ],
        ],
        'type' => [
            'label' => 'Тип',
        ],
        'starts_at' => [
            'label' => 'Начална дата',
        ],
        'ends_at' => [
            'label' => 'Крайна дата',
        ],
        'created_at' => [
            'label' => 'Създадена на',
        ],
        'coupon' => [
            'label' => 'Купон',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Наличност',
        ],
        'edit' => [
            'title' => 'Основна информация',
        ],
        'limitations' => [
            'label' => 'Ограничения',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Колекции',
            'description' => 'Изберете към кои колекции да бъде ограничена тази отстъпка.',
            'actions' => [
                'attach' => [
                    'label' => 'Свържи колекция',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'type' => [
                    'label' => 'Тип',
                    'limitation' => [
                        'label' => 'Ограничение',
                    ],
                    'exclusion' => [
                        'label' => 'Изключение',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ограничение',
                        ],
                        'exclusion' => [
                            'label' => 'Изключение',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Клиенти',
            'description' => 'Изберете към кои клиенти да бъде ограничена тази отстъпка.',
            'actions' => [
                'attach' => [
                    'label' => 'Свържи клиент',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Марките',
            'description' => 'Изберете към кои марки да бъде ограничена тази отстъпка.',
            'actions' => [
                'attach' => [
                    'label' => 'Свържи марка',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'type' => [
                    'label' => 'Тип',
                    'limitation' => [
                        'label' => 'Ограничение',
                    ],
                    'exclusion' => [
                        'label' => 'Изключение',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ограничение',
                        ],
                        'exclusion' => [
                            'label' => 'Изключение',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Продукти',
            'description' => 'Изберете към кои продукти да бъде ограничена тази отстъпка.',
            'actions' => [
                'attach' => [
                    'label' => 'Добави продукт',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'type' => [
                    'label' => 'Тип',
                    'limitation' => [
                        'label' => 'Ограничение',
                    ],
                    'exclusion' => [
                        'label' => 'Изключение',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ограничение',
                        ],
                        'exclusion' => [
                            'label' => 'Изключение',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Награди',
            'description' => 'Изберете кои продукти ще бъдат намалени, ако са в количката и условията са изпълнени.',
            'actions' => [
                'attach' => [
                    'label' => 'Добави награда',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'type' => [
                    'label' => 'Тип',
                    'limitation' => [
                        'label' => 'Ограничение',
                    ],
                    'exclusion' => [
                        'label' => 'Изключение',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ограничение',
                        ],
                        'exclusion' => [
                            'label' => 'Изключение',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Условия за продукти и варианти',
            'description' => 'Изберете условията за продукт или вариант, необходими за прилагане на отстъпката.',
            'actions' => [
                'attach' => [
                    'label' => 'Добави условие',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'type' => [
                    'label' => 'Тип',
                    'limitation' => [
                        'label' => 'Ограничение',
                    ],
                    'exclusion' => [
                        'label' => 'Изключение',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ограничение',
                        ],
                        'exclusion' => [
                            'label' => 'Изключение',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'Условия за колекции',
            'description' => 'Изберете условията за колекция, необходими за прилагане на отстъпката.',
            'actions' => [
                'attach' => [
                    'label' => 'Добави условие',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Варианти на продукти',
            'description' => 'Изберете към кои варианти на продукти да бъде ограничена тази отстъпка.',
            'actions' => [
                'attach' => [
                    'label' => 'Добави продукт вариант',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Име',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Опция(и)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ограничение',
                        ],
                        'exclusion' => [
                            'label' => 'Изключение',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
