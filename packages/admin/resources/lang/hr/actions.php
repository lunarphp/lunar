<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Stvori glavnu kolekciju',
        ],
        'create_child' => [
            'label' => 'Stvori podkolekciju',
        ],
        'move' => [
            'label' => 'Premjesti kolekciju',
        ],
        'delete' => [
            'label' => 'Izbriši',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Brisanje nije moguće',
                    'body' => 'Ova kolekcija sadrži podkolekcije i ne može se izbrisati.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Ažuriraj status',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'Mailer i obavijesti',
                    'no_mailers' => 'Za ovaj status nije dostupan nijedan mailer.',
                ],
                'step_three' => [
                    'label' => 'Pregled i spremanje',
                    'no_mailers' => 'Nijedan mailer nije odabran za pregled.',
                ],
            ],
            'notification' => [
                'label' => 'Status narudžbe ažuriran',
            ],
            'billing_email' => [
                'label' => 'E-mail za naplatu',
            ],
            'shipping_email' => [
                'label' => 'E-mail za dostavu',
            ],
        ],
    ],
];
