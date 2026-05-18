<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Създаване на основна колекция',
        ],
        'create_child' => [
            'label' => 'Създаване на подколекция',
        ],
        'move' => [
            'label' => 'Преместване на колекция',
        ],
        'delete' => [
            'label' => 'Изтриване',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Не може да бъде изтрита',
                    'body' => 'Тази колекция има подколекции и не може да бъде изтрита.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Актуализиране на статус',
            'wizard' => [
                'step_one' => [
                    'label' => 'Статус',
                ],
                'step_two' => [
                    'label' => 'Имейли и известия',
                    'no_mailers' => 'Няма налични имейли за този статус.',
                ],
                'step_three' => [
                    'label' => 'Преглед и запазване',
                    'no_mailers' => 'Не са избрани имейли за преглед.',
                ],
            ],
            'notification' => [
                'label' => 'Статусът на поръчката е актуализиран',
            ],
            'billing_email' => [
                'label' => 'Имейл за фактуриране',
            ],
            'shipping_email' => [
                'label' => 'Имейл за доставка',
            ],
        ],

    ],
];
