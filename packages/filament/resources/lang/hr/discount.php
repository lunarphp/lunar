<?php

return [
    'plural_label' => 'Popusti',
    'label' => 'Popust',
    'form' => [
        'promotion' => [
            'label' => 'Promocija',
            'helper_text' => 'Grupirajte ovaj popust u kampanju ili ostavite prazno za samostalni popust.',
        ],
        'conditions' => [
            'heading' => 'Uvjeti',
        ],
        'buy_x_get_y' => [
            'heading' => 'Kupi X, dobij Y',
        ],
        'amount_off' => [
            'heading' => 'Iznos popusta',
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'starts_at' => [
            'label' => 'Datum početka',
        ],
        'ends_at' => [
            'label' => 'Datum završetka',
        ],
        'priority' => [
            'label' => 'Prioritet',
            'helper_text' => 'Popusti s višim prioritetom primjenjuju se prvi.',
        ],
        'stop' => [
            'label' => 'Ne primjenjuj daljnje popuste nakon ovoga',
            'helper_text' => 'When this discount applies, any discount with a lower priority will be skipped. Give discounts different priorities to control the order they apply in.',
        ],
        'coupon' => [
            'label' => 'Kupon',
            'helper_text' => 'Unesite kupon koji je potreban kako bi se popust primijenio. Ako je prazno, popust se primjenjuje automatski.',
        ],
        'max_uses' => [
            'label' => 'Maksimalan broj korištenja',
            'helper_text' => 'Ostavite prazno za neograničen broj korištenja.',
        ],
        'max_uses_per_user' => [
            'label' => 'Maksimalan broj korištenja po korisniku',
            'helper_text' => 'Ostavite prazno za neograničen broj korištenja.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Minimalna vrijednost košarice',
        ],
        'min_qty' => [
            'label' => 'Količina proizvoda',
            'helper_text' => 'Odredite koliko je kvalificiranih proizvoda potrebno kako bi se popust primijenio.',
        ],
        'reward_qty' => [
            'label' => 'Broj besplatnih artikala',
            'helper_text' => 'Koliko se artikala od svakog umanjuje cijenom.',
        ],
        'max_reward_qty' => [
            'label' => 'Maksimalna količina nagrade',
            'helper_text' => 'Maksimalan broj proizvoda kojima se može odobriti popust neovisno o kriterijima.',
        ],
        'automatic_rewards' => [
            'label' => 'Automatski dodaj nagrade',
            'helper_text' => 'Uključite kako biste dodali nagradne proizvode ako se ne nalaze u košarici.',
        ],
        'fixed_value' => [
            'label' => 'Fixed value',
        ],
        'percentage' => [
            'label' => 'Percentage',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'status' => [
            'label' => 'Status',
            'active' => [
                'label' => 'Aktivan',
            ],
            'pending' => [
                'label' => 'Na čekanju',
            ],
            'expired' => [
                'label' => 'Istekao',
            ],
            'scheduled' => [
                'label' => 'Zakazan',
            ],
        ],
        'type' => [
            'label' => 'Tip',
        ],
        'starts_at' => [
            'label' => 'Datum početka',
        ],
        'ends_at' => [
            'label' => 'Datum završetka',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Dostupnost',
        ],
        'edit' => [
            'title' => 'Basic Information',
        ],
        'limitations' => [
            'label' => 'Ograničenja',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Kolekcije',
            'description' => 'Odaberite na koje kolekcije ovaj popust treba biti ograničen.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj kolekciju',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Ograničenje',
                    ],
                    'exclusion' => [
                        'label' => 'Isključenje',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograničenje',
                        ],
                        'exclusion' => [
                            'label' => 'Isključenje',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Customers',
            'description' => 'Select which customers this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Customer',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Brendovi',
            'description' => 'Odaberite na koje brendove ovaj popust treba biti ograničen.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj brend',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Ograničenje',
                    ],
                    'exclusion' => [
                        'label' => 'Izuzetak',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograničenje',
                        ],
                        'exclusion' => [
                            'label' => 'Izuzetak',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Proizvodi',
            'description' => 'Odaberite na koje proizvode ovaj popust treba biti ograničen.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj proizvod',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Ograničenje',
                    ],
                    'exclusion' => [
                        'label' => 'Izuzetak',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograničenje',
                        ],
                        'exclusion' => [
                            'label' => 'Izuzetak',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Nagradni proizvodi',
            'description' => 'Odaberite koji se proizvodi umanjuju popustom kada se nalaze u košarici i kada su gore navedeni uvjeti ispunjeni.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj proizvod',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Ograničenje',
                    ],
                    'exclusion' => [
                        'label' => 'Izuzetak',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograničenje',
                        ],
                        'exclusion' => [
                            'label' => 'Izuzetak',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Uvjeti proizvoda',
            'description' => 'Odaberite proizvode koji su potrebni kako bi se popust primijenio.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj proizvod',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Ograničenje',
                    ],
                    'exclusion' => [
                        'label' => 'Izuzetak',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograničenje',
                        ],
                        'exclusion' => [
                            'label' => 'Izuzetak',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'Collection Conditions',
            'description' => 'Select the collection conditions required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Condition',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Varijante proizvoda',
            'description' => 'Odaberite na koje varijante proizvoda ovaj popust treba biti ograničen.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj varijantu proizvoda',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naziv',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Opcija(e)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograničenje',
                        ],
                        'exclusion' => [
                            'label' => 'Izuzetak',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
