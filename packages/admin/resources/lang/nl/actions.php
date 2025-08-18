<?php

return [
    'collections' => [
        'create_root' => [
            'label' => 'Maak Hoofdcategorie',
        ],
        'create_child' => [
            'label' => 'Maak Subcategorie',
        ],
        'move' => [
            'label' => 'Verplaats Categorie',
        ],
        'delete' => [
            'label' => 'Verwijderen',
            'notifications' => [
                'cannot_delete' => [
                    'title' => 'Kan niet verwijderen',
                    'body' => 'Deze collectie heeft onderliggende collecties en kan niet worden verwijderd.',
                ],
            ],
        ],
    ],
    'orders' => [
        'update_status' => [
            'label' => 'Status Bijwerken',
            'wizard' => [
                'step_one' => [
                    'label' => 'Status',
                ],
                'step_two' => [
                    'label' => 'Mailers & Meldingen',
                    'no_mailers' => 'Er zijn geen mailers beschikbaar voor deze status.',
                ],
                'step_three' => [
                    'label' => 'Voorbeeld & Opslaan',
                    'no_mailers' => 'Er zijn geen mailers gekozen voor voorbeeld.',
                ],
            ],
            'notification' => [
                'label' => 'Orderstatus bijgewerkt',
            ],
            'billing_email' => [
                'label' => 'Facturatie E-mail',
            ],
            'shipping_email' => [
                'label' => 'Verzend E-mail',
            ],
        ],

    ],
];
