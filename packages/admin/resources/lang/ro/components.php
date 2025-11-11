<?php

return [
    'tags' => [
        'notification' => [

            'updated' => 'Etichetele au fost actualizate',

        ],
    ],

    'activity-log' => [

        'input' => [

            'placeholder' => 'Adaugă un comentariu',

        ],

        'action' => [

            'add-comment' => 'Adaugă comentariu',

        ],

        'system' => 'Sistem',

        'partials' => [
            'orders' => [
                'order_created' => 'Comandă creată',

                'status_change' => 'Stare actualizată',

                'capture' => 'Plată de :amount pe cardul care se termină cu :last_four',

                'authorized' => 'Autorizare de :amount pe cardul care se termină cu :last_four',

                'refund' => 'Rambursare de :amount pe cardul care se termină cu :last_four',

                'address' => ':type actualizată',

                'billingAddress' => 'Adresă de facturare',

                'shippingAddress' => 'Adresă de livrare',
            ],

            'update' => [
                'updated' => ':model actualizat',
            ],

            'create' => [
                'created' => ':model creat',
            ],

            'tags' => [
                'updated' => 'Etichetele au fost actualizate',
                'added' => 'Adăugat',
                'removed' => 'Eliminat',
            ],
        ],

        'notification' => [
            'comment_added' => 'Comentariu adăugat',
        ],

    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Introduceți ID-ul videoclipului YouTube, ex.: dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Colecție părinte',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Colecțiile au fost reordonate',
            ],
            'node-expanded' => [
                'danger' => 'Nu s-au putut încărca colecțiile',
            ],
            'delete' => [
                'danger' => 'Nu se poate șterge colecția',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Adaugă opțiune',
        ],
        'delete-option' => [
            'label' => 'Șterge opțiunea',
        ],
        'remove-shared-option' => [
            'label' => 'Elimină opțiunea partajată',
        ],
        'add-value' => [
            'label' => 'Adaugă altă valoare',
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'values' => [
            'label' => 'Valori',
        ],
    ],
];
