<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Utwórz główną kolekcję',
        ],
        'create_child' => [
            'label' => 'Utwórz podkolekcję',
        ],
        'move' => [
            'label' => 'Przenieś kolekcję',
        ],
        'delete' => [
            'label' => 'Usuń',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Nie można usunąć',
                    'body' => 'Ta kolekcja zawiera kolekcje podrzędne i nie może zostać usunięta.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Zaktualizuj status',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'Wybierz mailer',
                    'no_mailers' => 'Brak mailerów do wyboru.',
                ],
                'step_three' => [
                    'label' => 'Podejrzyj i zapisz',
                    'no_mailers' => 'Do podglądu nie wybrano żadnych mailerów.',
                ],
            ],
            'notification' => [
                'label' => 'Status zamówienia został zaktualizowany',
            ],
            'billing_email' => [
                'label' => 'Email do faktury',
            ],
            'shipping_email' => [
                'label' => 'Email do wysyłki',
            ],
        ],

    ],
];
