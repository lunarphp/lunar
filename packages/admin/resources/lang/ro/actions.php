<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Creează colecție rădăcină',
        ],
        'create_child' => [
            'label' => 'Creează colecție copil',
        ],
        'move' => [
            'label' => 'Mută colecția',
        ],
        'delete' => [
            'label' => 'Șterge',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Nu se poate șterge',
                    'body' => 'Această colecție are colecții copil și nu poate fi ștearsă.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Actualizează starea',
            'wizard' => [
                'step_one' => [
                    'label' => 'Stare',
                ],
                'step_two' => [
                    'label' => 'E-mailuri și notificări',
                    'no_mailers' => 'Nu există e-mailuri disponibile pentru această stare.',
                ],
                'step_three' => [
                    'label' => 'Previzualizare și salvare',
                    'no_mailers' => 'Nu s-au ales e-mailuri pentru previzualizare.',
                ],
            ],
            'notification' => [
                'label' => 'Starea comenzii a fost actualizată',
            ],
            'billing_email' => [
                'label' => 'E-mail facturare',
            ],
            'shipping_email' => [
                'label' => 'E-mail livrare',
            ],
        ],

    ],
];
