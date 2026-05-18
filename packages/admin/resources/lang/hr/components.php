<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Oznake ažurirane',
        ],
    ],

    'activity-log' => [
        'input' => [
            'placeholder' => 'Dodaj komentar',
        ],

        'action' => [
            'add-comment' => 'Dodaj komentar',
        ],

        'system' => 'Sustav',

        'partials' => [
            'orders' => [
                'order_created' => 'Narudžba izrađena',
                'status_change' => 'Status ažuriran',
                'capture' => 'Naplata od :amount na kartici sa završnim znamenkama :last_four',
                'authorized' => 'Autorizacija od :amount na kartici sa završnim znamenkama :last_four',
                'refund' => 'Povrat od :amount na kartici sa završnim znamenkama :last_four',
                'address' => ':type ažurirano',
                'billingAddress' => 'Adresa za naplatu',
                'shippingAddress' => 'Adresa za dostavu',
            ],
            'update' => [
                'updated' => ':model ažuriran',
            ],
            'create' => [
                'created' => ':model izrađen',
            ],
            'tags' => [
                'updated' => 'Oznake ažurirane',
                'added' => 'Dodano',
                'removed' => 'Uklonjeno',
            ],
        ],

        'notification' => [
            'comment_added' => 'Komentar dodan',
        ],
    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Unesite ID YouTube videozapisa, npr. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Nadređena kolekcija',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Kolekcije ponovno poredane',
            ],
            'node-expanded' => [
                'danger' => 'Kolekcije nije moguće učitati',
            ],
            'delete' => [
                'danger' => 'Kolekciju nije moguće izbrisati',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Dodaj opciju',
        ],
        'delete-option' => [
            'label' => 'Izbriši opciju',
        ],
        'remove-shared-option' => [
            'label' => 'Ukloni zajedničku opciju',
        ],
        'add-value' => [
            'label' => 'Dodaj još jednu vrijednost',
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'values' => [
            'label' => 'Vrijednosti',
        ],
    ],
];
