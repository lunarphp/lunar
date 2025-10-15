<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Rendeld hozzá a vásárlói csoportokat ehhez a szállítási módhoz az elérhetőség meghatározásához.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Szállítási díjak',
        'actions' => [
            'create' => [
                'label' => 'Szállítási díj létrehozása',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Minden ár tartalmazza az adót, ezt figyelembe vesszük a minimális költés számításakor.',
            'prices_excl_tax' => 'Minden ár adó nélkül értendő, a minimális költés a kosár részösszege alapján kerül kiszámításra.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Szállítási mód',
            ],
            'price' => [
                'label' => 'Ár',
            ],
            'prices' => [
                'label' => 'Árlépcsők',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Vásárlói csoport',
                        'placeholder' => 'Bármely',
                    ],
                    'currency_id' => [
                        'label' => 'Pénznem',
                    ],
                    'min_spend' => [
                        'label' => 'Min. költés',
                    ],
                    'min_weight' => [
                        'label' => 'Min. súly',
                    ],
                    'price' => [
                        'label' => 'Ár',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Szállítási mód',
            ],
            'price' => [
                'label' => 'Ár',
            ],
            'price_breaks_count' => [
                'label' => 'Árlépcsők',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Szállítási kizárások',
        'form' => [
            'purchasable' => [
                'label' => 'Termék',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Szállítási kizárási lista hozzáadása',
            ],
            'attach' => [
                'label' => 'Kizárási lista hozzáadása',
            ],
            'detach' => [
                'label' => 'Eltávolítás',
            ],
        ],
    ],
];
