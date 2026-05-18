<?php

return [
    'tags' => [
        'notification' => [

            'updated' => 'Етикетите са актуализирани',

        ],
    ],

    'activity-log' => [

        'input' => [

            'placeholder' => 'Добавете коментар',

        ],

        'action' => [

            'add-comment' => 'Добавяне на коментар',

        ],

        'system' => 'Система',

        'partials' => [
            'orders' => [
                'order_created' => 'Поръчката е създадена',

                'status_change' => 'Статусът е актуализиран',

                'capture' => 'Плащане от :amount с карта, завършваща на :last_four',

                'authorized' => 'Удобрено плащане :amount с карта, завършваща на :last_four',

                'refund' => 'Възстановяване на :amount с карта, завършваща на :last_four',

                'address' => ':type е актуализиран',

                'billingAddress' => 'Адрес за фактуриране',

                'shippingAddress' => 'Адрес за доставка',
            ],

            'update' => [
                'updated' => ':model е актуализиран',
            ],

            'create' => [
                'created' => ':model е създаден',
            ],

            'tags' => [
                'updated' => 'Етикетите са актуализирани',
                'added' => 'Добавено',
                'removed' => 'Премахнато',
            ],
        ],

        'notification' => [
            'comment_added' => 'Коментарът е добавен',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Въведете ID на YouTube видеото, напр. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Родителска колекция',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Колекциите са пренаредени',
            ],
            'node-expanded' => [
                'danger' => 'Неуспешно зареждане на колекциите',
            ],
            'delete' => [
                'danger' => 'Неуспешно изтриване на колекция',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Добавяне на опция',
        ],
        'delete-option' => [
            'label' => 'Изтриване на опция',
        ],
        'remove-shared-option' => [
            'label' => 'Премахване на споделена опция',
        ],
        'add-value' => [
            'label' => 'Добавяне на още една стойност',
        ],
        'name' => [
            'label' => 'Име',
        ],
        'values' => [
            'label' => 'Стойности',
        ],
    ],
];
