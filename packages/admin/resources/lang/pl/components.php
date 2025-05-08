<?php

return [
    'tags' => [
        'notification' => [

            'updated' => 'Tagi zaktualizowane',

        ],
    ],

    'activity-log' => [

        'input' => [

            'placeholder' => 'Dodaj komentarz',

        ],

        'action' => [

            'add-comment' => 'Dodaj komentarz',

        ],

        'system' => 'System',

        'partials' => [
            'orders' => [
                'order_created' => 'Zamówienie utworzone',

                'status_change' => 'Status zaktualizowany',

                'capture' => 'Płatność w wysokości :amount na karcie kończącej się na :last_four',

                'authorized' => 'Autoryzacja płatności w wysokości :amount na karcie kończącej się na :last_four',

                'refund' => 'Zwrot płatności w wysokości :amount na karcie kończącej się na :last_four',

                'address' => ':type zaktualizowany',

                'billingAddress' => 'Adres rozliczeniowy',

                'shippingAddress' => 'Adres dostawy',
            ],

            'update' => [
                'updated' => ':model zaktualizowany',
            ],

            'create' => [
                'created' => ':model utworzony',
            ],

            'tags' => [
                'updated' => 'Tagi zaktualizowane',
                'added' => 'Dodano',
                'removed' => 'Usunięto',
            ],
        ],

        'notification' => [
            'comment_added' => 'Komentarz dodany',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Dodaj ID filmu z YouTube. np. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Kolekcja nadrzędna',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Kolekcje zostały posortowane',
            ],
            'node-expanded' => [
                'danger' => 'Nie można załadować kolekcji',
            ],
            'delete' => [
                'danger' => 'Nie można usunąć kolekcji',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Dodaj opcję',
        ],
        'delete-option' => [
            'label' => 'Usuń opcję',
        ],
        'remove-shared-option' => [
            'label' => 'Usuń wspólną opcję',
        ],
        'add-value' => [
            'label' => 'Dodaj kolejną wartość',
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'values' => [
            'label' => 'Wartości',
        ],
    ],
];
