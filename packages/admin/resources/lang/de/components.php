<?php

return [
    'tags' => [
        'notification' => [
            'updated' => 'Tags aktualisiert',
        ],
    ],

    'activity-log' => [
        'input' => [
            'placeholder' => 'Einen Kommentar hinzufügen',
        ],

        'action' => [
            'add-comment' => 'Kommentar hinzufügen',
        ],

        'system' => 'System',

        'partials' => [
            'orders' => [
                'order_created' => 'Bestellung erstellt',
                'status_change' => 'Status aktualisiert',
                'capture' => 'Zahlung von :amount auf Karte mit Endung :last_four',
                'authorized' => 'Autorisierung von :amount auf Karte mit Endung :last_four',
                'refund' => 'Rückerstattung von :amount auf Karte mit Endung :last_four',
                'address' => ':type aktualisiert',
                'billingAddress' => 'Rechnungsadresse',
                'shippingAddress' => 'Lieferadresse',
            ],
            'update' => [
                'updated' => ':model aktualisiert',
            ],
            'create' => [
                'created' => ':model erstellt',
            ],
            'tags' => [
                'updated' => 'Tags aktualisiert',
                'added' => 'Hinzugefügt',
                'removed' => 'Entfernt',
            ],
        ],

        'notification' => [
            'comment_added' => 'Kommentar hinzugefügt',
        ],
    ],

    'forms' => [
        'youtube' => [
            'helperText' => 'Geben Sie die ID des YouTube-Videos ein, z.B. dQw4w9WgXcQ',
        ],
    ],

    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Übergeordnete Sammlung',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Sammlungen neu geordnet',
            ],
            'node-expanded' => [
                'danger' => 'Sammlungen konnten nicht geladen werden',
            ],
            'delete' => [
                'danger' => 'Sammlung konnte nicht gelöscht werden',
            ],
        ],
    ],

    'product-options-list' => [
        'add-option' => [
            'label' => 'Option hinzufügen',
        ],
        'delete-option' => [
            'label' => 'Option löschen',
        ],
        'remove-shared-option' => [
            'label' => 'Gemeinsame Option entfernen',
        ],
        'add-value' => [
            'label' => 'Weiteren Wert hinzufügen',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'values' => [
            'label' => 'Werte',
        ],
    ],
];
