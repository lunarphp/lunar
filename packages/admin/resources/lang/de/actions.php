<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Stammsammlung erstellen',
        ],
        'create_child' => [
            'label' => 'Untergeordnete Sammlung erstellen',
        ],
        'move' => [
            'label' => 'Sammlung verschieben',
        ],
        'delete' => [
            'label' => 'Löschen',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Löschen nicht möglich',
                    'body' => 'Diese Sammlung enthält untergeordnete Sammlungen und kann nicht gelöscht werden.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Status aktualisieren',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'Mailer & Benachrichtigungen',
                    'no_mailers' => 'Es sind keine Mailer für diesen Status verfügbar.',
                ],
                'step_three' => [
                    'label' => 'Vorschau & Speichern',
                    'no_mailers' => 'Es wurden keine Mailer zur Vorschau ausgewählt.',
                ],
            ],
            'notification' => [
                'label' => 'Bestellstatus aktualisiert',
            ],
            'billing_email' => [
                'label' => 'Rechnungs-E-Mail',
            ],
            'shipping_email' => [
                'label' => 'Versand-E-Mail',
            ],
        ],
    ],
];
