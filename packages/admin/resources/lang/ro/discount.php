<?php

return [
    'plural_label' => 'Reduceri',
    'label' => 'Reducere',
    'form' => [
        'conditions' => [
            'heading' => 'Condiții',
        ],
        'buy_x_get_y' => [
            'heading' => 'Cumperi X, primești Y',
        ],
        'amount_off' => [
            'heading' => 'Reducere sumă',
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'handle' => [
            'label' => 'Identificator',
        ],
        'starts_at' => [
            'label' => 'Data de început',
        ],
        'ends_at' => [
            'label' => 'Data de sfârșit',
        ],
        'priority' => [
            'label' => 'Prioritate',
            'helper_text' => 'Reducerile cu prioritate mai mare vor fi aplicate primele.',
            'options' => [
                'low' => [
                    'label' => 'Scăzută',
                ],
                'medium' => [
                    'label' => 'Mediu',
                ],
                'high' => [
                    'label' => 'Ridicată',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Oprește aplicarea altor reduceri după aceasta',
        ],
        'coupon' => [
            'label' => 'Cupon',
            'helper_text' => 'Introduceți cuponul necesar pentru aplicarea reducerii; dacă este lăsat gol, se aplică automat.',
        ],
        'max_uses' => [
            'label' => 'Utilizări maxime',
            'helper_text' => 'Lăsați gol pentru utilizări nelimitate.',
        ],
        'max_uses_per_user' => [
            'label' => 'Utilizări maxime per utilizator',
            'helper_text' => 'Lăsați gol pentru utilizări nelimitate.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Valoare minimă coș',
        ],
        'min_qty' => [
            'label' => 'Cantitate produs',
            'helper_text' => 'Stabiliți câte produse eligibile sunt necesare pentru aplicarea reducerii.',
        ],
        'reward_qty' => [
            'label' => 'Nr. articole gratuite',
            'helper_text' => 'Câte unități din fiecare articol sunt reduse.',
        ],
        'max_reward_qty' => [
            'label' => 'Cantitate maximă recompensă',
            'helper_text' => 'Numărul maxim de produse ce pot fi reduse, indiferent de criterii.',
        ],
        'automatic_rewards' => [
            'label' => 'Adaugă automat recompense',
            'helper_text' => 'Activați pentru a adăuga produse-recompensă când nu sunt în coș.',
        ],
        'fixed_value' => [
            'label' => 'Valoare fixă',
        ],
        'percentage' => [
            'label' => 'Procent',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'status' => [
            'label' => 'Stare',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Activă',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'În așteptare',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Expirată',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Programată',
            ],
        ],
        'type' => [
            'label' => 'Tip',
        ],
        'starts_at' => [
            'label' => 'Data de început',
        ],
        'ends_at' => [
            'label' => 'Data de sfârșit',
        ],
        'created_at' => [
            'label' => 'Creat la',
        ],
        'coupon' => [
            'label' => 'Cupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Disponibilitate',
        ],
        'edit' => [
            'title' => 'Informații de bază',
        ],
        'limitations' => [
            'label' => 'Limitări',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Colecții',
            'description' => 'Selectați colecțiile la care se limitează această reducere.',
            'actions' => [
                'attach' => [
                    'label' => 'Atașează colecție',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Limitare',
                    ],
                    'exclusion' => [
                        'label' => 'Excludere',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitare',
                        ],
                        'exclusion' => [
                            'label' => 'Excludere',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Clienți',
            'description' => 'Selectați clienții la care se limitează această reducere.',
            'actions' => [
                'attach' => [
                    'label' => 'Atașează client',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Mărci',
            'description' => 'Selectați mărcile la care se limitează această reducere.',
            'actions' => [
                'attach' => [
                    'label' => 'Atașează marcă',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Limitare',
                    ],
                    'exclusion' => [
                        'label' => 'Excludere',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitare',
                        ],
                        'exclusion' => [
                            'label' => 'Excludere',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Produse',
            'description' => 'Selectați produsele la care se limitează această reducere.',
            'actions' => [
                'attach' => [
                    'label' => 'Adaugă produs',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Limitare',
                    ],
                    'exclusion' => [
                        'label' => 'Excludere',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitare',
                        ],
                        'exclusion' => [
                            'label' => 'Excludere',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Recompense',
            'description' => 'Selectați produsele care vor fi reduse dacă sunt în coș și sunt îndeplinite condițiile de mai sus.',
            'actions' => [
                'attach' => [
                    'label' => 'Adaugă recompensă',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Limitare',
                    ],
                    'exclusion' => [
                        'label' => 'Excludere',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitare',
                        ],
                        'exclusion' => [
                            'label' => 'Excludere',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Condiții',
            'description' => 'Selectați condițiile necesare pentru aplicarea reducerii.',
            'actions' => [
                'attach' => [
                    'label' => 'Adaugă condiție',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'type' => [
                    'label' => 'Tip',
                    'limitation' => [
                        'label' => 'Limitare',
                    ],
                    'exclusion' => [
                        'label' => 'Excludere',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitare',
                        ],
                        'exclusion' => [
                            'label' => 'Excludere',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Variante de produs',
            'description' => 'Selectați variantele de produs la care se limitează această reducere.',
            'actions' => [
                'attach' => [
                    'label' => 'Adaugă variantă de produs',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nume',
                ],
                'sku' => [
                    'label' => 'Cod stoc intern (SKU)',
                ],
                'values' => [
                    'label' => 'Opțiune(i)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Limitare',
                        ],
                        'exclusion' => [
                            'label' => 'Excludere',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
