<?php

return [
    'public_id' => [
        'label' => 'Public ID',
    ],

    'tags' => [
        'notification' => [
            'updated' => 'Címkék frissítve',
        ],
    ],
    'activity-log' => [
        'input' => [
            'placeholder' => 'Megjegyzés hozzáadása',
        ],
        'action' => [
            'add-comment' => 'Megjegyzés hozzáadása',
        ],
        'system' => 'Rendszer',
        'partials' => [
            'orders' => [
                'order_created' => 'Rendelés létrehozva',
                'status_change' => 'Státusz frissítve',
                'order_closed' => 'Rendelés lezárva',
                'order_reopened' => 'Rendelés újranyitva',
                'order_cancelled' => 'Rendelés törölve (:reason)',
                'order_cancelled_no_reason' => 'Rendelés törölve',

                'email_notification' => 'Sent :notification to :email',

                'email_notification_fallback' => 'a notification',
                'fulfilment_state' => ':id. számú teljesítés megjelölve mint :state',
                'fulfilment_held' => ':id. számú teljesítés felfüggesztve (:reason)',
                'fulfilment_held_no_reason' => ':id. számú teljesítés felfüggesztve',
                'fulfilment_released' => ':id. számú teljesítés felfüggesztése feloldva',

                'capture' => 'Fizetés: :amount, kártya utolsó négy számjegye: :last_four',
                'authorized' => 'Engedélyezve: :amount, kártya utolsó négy számjegye: :last_four',
                'refund' => 'Visszatérítés: :amount, kártya utolsó négy számjegye: :last_four',
                'address' => ':type frissítve',
                'billingAddress' => 'Számlázási cím',
                'shippingAddress' => 'Szállítási cím',
            ],
            'update' => [
                'updated' => ':model frissítve',
            ],
            'create' => [
                'created' => ':model létrehozva',
            ],
            'tags' => [
                'updated' => 'Címkék frissítve',
                'added' => 'Hozzáadva',
                'removed' => 'Eltávolítva',
            ],
        ],
        'notification' => [
            'comment_added' => 'Megjegyzés hozzáadva',
        ],
    ],
    'forms' => [
        'youtube' => [
            'helperText' => 'Add meg a YouTube videó azonosítóját. pl.: dQw4w9WgXcQ',
        ],
    ],
    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Szülő gyűjtemény',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Gyűjtemények át-rendezve',
            ],
            'node-expanded' => [
                'danger' => 'Nem lehet betölteni a gyűjteményeket',
            ],
            'delete' => [
                'danger' => 'Nem lehet törölni a gyűjteményt',
            ],
        ],
    ],
    'product-options-list' => [
        'add-option' => [
            'label' => 'Opció hozzáadása',
        ],
        'delete-option' => [
            'label' => 'Opció törlése',
        ],
        'remove-shared-option' => [
            'label' => 'Megosztott opció eltávolítása',
        ],
        'add-value' => [
            'label' => 'Új érték hozzáadása',
        ],
        'name' => [
            'label' => 'Név',
        ],
        'values' => [
            'label' => 'Értékek',
        ],
    ],
];
