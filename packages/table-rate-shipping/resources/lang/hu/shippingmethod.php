<?php

return [
    'label_plural' => 'Szállítási módok',
    'label' => 'Szállítási mód',
    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'description' => [
            'label' => 'Leírás',
        ],
        'code' => [
            'label' => 'Kód',
        ],
        'cutoff' => [
            'label' => 'Határidő',
        ],
        'charge_by' => [
            'label' => 'Számlázás',
            'options' => [
                'cart_total' => 'Kosár végösszeg',
                'weight' => 'Súly',
            ],
        ],
        'driver' => [
            'label' => 'Típus',
            'options' => [
                'ship-by' => 'Házhozszállítás',
                'collection' => 'Személyes átvétel',
                'flat-rate' => 'Átalánydíjas',
                'free-shipping' => 'Ingyenes szállítás',
            ],
        ],
        'stock_available' => [
            'label' => 'A kosárban lévő összes terméknek raktáron kell lennie',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'code' => [
            'label' => 'Kód',
        ],
        'driver' => [
            'label' => 'Típus',
            'options' => [
                'ship-by' => 'Házhozszállítás',
                'collection' => 'Személyes átvétel',
                'flat-rate' => 'Átalánydíjas',
                'free-shipping' => 'Ingyenes szállítás',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Elérhetőség',
            'customer_groups' => 'Ez a szállítási mód jelenleg egyik vásárlói csoport számára sem elérhető.',
        ],
    ],
];
