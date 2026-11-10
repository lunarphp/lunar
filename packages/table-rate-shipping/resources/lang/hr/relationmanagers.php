<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Povežite grupe kupaca s ovim načinom dostave kako biste odredili njegovu dostupnost.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Stope dostave',
        'actions' => [
            'create' => [
                'label' => 'Stvori stopu dostave',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Sve cijene uključuju porez, koji će biti uzet u obzir prilikom izračuna minimalne potrošnje.',
            'prices_excl_tax' => 'Sve cijene su bez poreza, minimalna potrošnja temelji se na međuzbroju košarice.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Način dostave',
            ],
            'price' => [
                'label' => 'Cijena',
            ],
            'prices' => [
                'label' => 'Cjenovni razredi',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Grupa kupaca',
                        'placeholder' => 'Bilo koja',
                    ],
                    'currency_id' => [
                        'label' => 'Valuta',
                    ],
                    'min_spend' => [
                        'label' => 'Min. potrošnja',
                    ],
                    'min_weight' => [
                        'label' => 'Min. težina',
                        'helper_text' => 'Unesite težinu u :unit',
                    ],
                    'price' => [
                        'label' => 'Cijena',
                    ],
                ],
            ],
        ],
        'table' => [
            'enabled' => [
                'label' => 'Omogućeno',
            ],
            'disabled' => [
                'label' => 'onemogućeno',
            ],
            'shipping_method' => [
                'label' => 'Način dostave',
                'disabled' => 'Onemogućeno',
            ],
            'price' => [
                'label' => 'Cijena',
            ],
            'price_breaks_count' => [
                'label' => 'Cjenovni razredi',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Isključenja dostave',
        'form' => [
            'purchasable' => [
                'label' => 'Proizvod',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Dodaj popis isključenja dostave',
            ],
            'attach' => [
                'label' => 'Dodaj popis isključenja',
            ],
            'detach' => [
                'label' => 'Ukloni',
            ],
        ],
    ],
];
