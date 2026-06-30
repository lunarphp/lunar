<?php

return [
    'public_id' => [
        'label' => 'Public ID',
    ],

    'tags' => [
        'notification' => [
            'updated' => 'Tags bijgewerkt',
        ],
    ],
    'activity-log' => [
        'input' => [
            'placeholder' => 'Voeg een opmerking toe',
        ],
        'action' => [
            'add-comment' => 'Voeg opmerking toe',
        ],
        'system' => 'Systeem',
        'partials' => [
            'orders' => [
                'order_created' => 'Bestelling aangemaakt',
                'status_change' => 'Status bijgewerkt',
                'order_closed' => 'Bestelling gesloten',
                'order_reopened' => 'Bestelling heropend',
                'order_cancelled' => 'Bestelling geannuleerd (:reason)',
                'order_cancelled_no_reason' => 'Bestelling geannuleerd',

                'email_notification' => 'Sent :notification to :email',

                'email_notification_fallback' => 'a notification',
                'fulfilment_state' => 'Fulfilment #:id gemarkeerd als :state',
                'fulfilment_held' => 'Fulfilment #:id in de wacht gezet (:reason)',
                'fulfilment_held_no_reason' => 'Fulfilment #:id in de wacht gezet',
                'fulfilment_released' => 'Fulfilment #:id uit de wacht gehaald',

                'capture' => 'Betaling van :amount op kaart eindigend op :last_four',
                'authorized' => 'Geautoriseerd bedrag van :amount op kaart eindigend op :last_four',
                'refund' => 'Terugbetaling van :amount op kaart eindigend op :last_four',
                'address' => ':type bijgewerkt',
                'billingAddress' => 'Factuuradres',
                'shippingAddress' => 'Verzendadres',
            ],
            'update' => [
                'updated' => ':model bijgewerkt',
            ],
            'create' => [
                'created' => ':model aangemaakt',
            ],
            'tags' => [
                'updated' => 'Tags bijgewerkt',
                'added' => 'Toegevoegd',
                'removed' => 'Verwijderd',
            ],
        ],
        'notification' => [
            'comment_added' => 'Opmerking toegevoegd',
        ],
    ],
    'forms' => [
        'youtube' => [
            'helperText' => 'Voer de ID van de YouTube-video in. bijv. dQw4w9WgXcQ',
        ],
    ],
    'collection-tree-view' => [
        'actions' => [
            'move' => [
                'form' => [
                    'target_id' => [
                        'label' => 'Bovenliggende collectie',
                    ],
                ],
            ],
        ],
        'notifications' => [
            'collections-reordered' => [
                'success' => 'Collecties opnieuw gerangschikt',
            ],
            'node-expanded' => [
                'danger' => 'Kan collecties niet laden',
            ],
            'delete' => [
                'danger' => 'Kan collectie niet verwijderen',
            ],
        ],
    ],
    'product-options-list' => [
        'add-option' => [
            'label' => 'Optie toevoegen',
        ],
        'delete-option' => [
            'label' => 'Optie verwijderen',
        ],
        'remove-shared-option' => [
            'label' => 'Gedeelde optie verwijderen',
        ],
        'add-value' => [
            'label' => 'Nog een waarde toevoegen',
        ],
        'name' => [
            'label' => 'Naam',
        ],
        'values' => [
            'label' => 'Waarden',
        ],
    ],
];
