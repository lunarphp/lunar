<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Свържете клиентски групи с този метод за доставка, за да определите неговата наличност.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Тарифни ставки за доставка',
        'actions' => [
            'create' => [
                'label' => 'Създай тарифа за доставка',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Всички цени включват данък, който ще бъде взет предвид при изчисляване на минималния разход.',
            'prices_excl_tax' => 'Всички цени са без данък, минималният разход ще бъде базиран на междинната сума в количката.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Метод за доставка',
            ],
            'price' => [
                'label' => 'Цена',
            ],
            'prices' => [
                'label' => 'Ценови нива',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Клиентска група',
                        'placeholder' => 'Всички',
                    ],
                    'currency_id' => [
                        'label' => 'Валута',
                    ],
                    'min_spend' => [
                        'label' => 'Мин. разход',
                    ],
                    'min_weight' => [
                        'label' => 'Мин. тегло',
                        'helper_text' => 'Въведете теглото в :unit',
                    ],
                    'price' => [
                        'label' => 'Цена',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'Активирано',
            ],
            'disabled' => [
                'label' => 'деактивирано',
            ],
            'shipping_method' => [
                'label' => 'Метод за доставка',
                'disabled' => 'Деактивирано',
            ],
            'price' => [
                'label' => 'Цена',
            ],
            'price_breaks_count' => [
                'label' => 'Ценови нива',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Ограничения при доставка',
        'form' => [
            'purchasable' => [
                'label' => 'Продукт',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Добави списък с ограничения за доставка',
            ],
            'attach' => [
                'label' => 'Добави списък с ограничения',
            ],
            'detach' => [
                'label' => 'Премахни',
            ],
        ],
    ],
];
