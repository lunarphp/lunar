<?php

return [
    'label_plural' => 'Načini dostave',
    'label' => 'Način dostave',
    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'description' => [
            'label' => 'Opis',
        ],
        'code' => [
            'label' => 'Šifra',
        ],
        'schedule' => [
            'label' => 'Raspored dostupnosti',
            'days' => [
                'monday' => 'Ponedjeljak',
                'tuesday' => 'Utorak',
                'wednesday' => 'Srijeda',
                'thursday' => 'Četvrtak',
                'friday' => 'Petak',
                'saturday' => 'Subota',
                'sunday' => 'Nedjelja',
            ],
            'from' => [
                'label' => 'Od',
            ],
            'to' => [
                'label' => 'Do',
                'validation' => [
                    'after' => 'Vrijeme "do" mora biti nakon vremena "od".',
                ],
            ],
        ],
        'charge_by' => [
            'label' => 'Obračun prema',
            'options' => [
                'cart_total' => 'Ukupnom iznosu košarice',
                'weight' => 'Težini',
            ],
        ],
        'driver' => [
            'label' => 'Tip',
            'options' => [
                'ship-by' => 'Standardno',
                'collection' => 'Preuzimanje',
            ],
        ],
        'stock_available' => [
            'label' => 'Sve stavke u košarici moraju biti na zalihi',
        ],
        'weight_unit' => [
            'label' => 'Mjerna jedinica težine',
            'placeholder' => 'Bez ograničenja težine',
        ],
        'min_weight' => [
            'label' => 'Minimalna težina',
        ],
        'max_weight' => [
            'label' => 'Maksimalna težina',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'code' => [
            'label' => 'Šifra',
        ],
        'driver' => [
            'label' => 'Tip',
            'options' => [
                'ship-by' => 'Standardno',
                'collection' => 'Preuzimanje',
            ],
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Dostupnost',
            'customer_groups' => 'Ovaj način dostave trenutno nije dostupan ni jednoj grupi kupaca.',
        ],
    ],
];
