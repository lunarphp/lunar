<?php

return [
    'plural_label' => 'Kedvezmények',
    'label' => 'Kedvezmény',
    'form' => [
        'conditions' => [
            'heading' => 'Feltételek',
        ],
        'buy_x_get_y' => [
            'heading' => 'Vásárolj X-et, kapj Y-t',
        ],
        'amount_off' => [
            'heading' => 'Összeg alapú kedvezmény',
        ],
        'name' => [
            'label' => 'Név',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'starts_at' => [
            'label' => 'Kezdés dátuma',
        ],
        'ends_at' => [
            'label' => 'Befejezés dátuma',
        ],
        'priority' => [
            'label' => 'Prioritás',
            'helper_text' => 'A magasabb prioritású kedvezmények kerülnek először alkalmazásra.',
            'options' => [
                'low' => [
                    'label' => 'Alacsony',
                ],
                'medium' => [
                    'label' => 'Közepes',
                ],
                'high' => [
                    'label' => 'Magas',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Más kedvezmények alkalmazásának megakadályozása ez után',
        ],
        'coupon' => [
            'label' => 'Kupon',
            'helper_text' => 'Add meg a kuponkódot, amely szükséges a kedvezmény alkalmazásához; üresen hagyva automatikusan alkalmazódik.',
        ],
        'max_uses' => [
            'label' => 'Max felhasználás',
            'helper_text' => 'Hagyd üresen a korlátlan felhasználáshoz.',
        ],
        'max_uses_per_user' => [
            'label' => 'Max felhasználás felhasználónként',
            'helper_text' => 'Hagyd üresen a korlátlan felhasználáshoz.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Minimum kosárérték',
        ],
        'min_qty' => [
            'label' => 'Termék mennyisége',
            'helper_text' => 'Add meg, hány jogosult termék szükséges a kedvezmény érvényesítéséhez.',
        ],
        'reward_qty' => [
            'label' => 'Ingyenes tételek száma',
            'helper_text' => 'Termékenként hány darab jár ingyen.',
        ],
        'max_reward_qty' => [
            'label' => 'Maximális jutalom mennyiség',
            'helper_text' => 'A maximális termékmennyiség, amely kedvezményben részesíthető, függetlenül a feltételektől.',
        ],
        'automatic_rewards' => [
            'label' => 'Jutalmak automatikus hozzáadása',
            'helper_text' => 'Kapcsolja be, hogy a jutalomtermékek automatikusan hozzáadódjanak, ha nincsenek a kosárban.',
        ],
        'fixed_value' => [
            'label' => 'Rögzített érték',
        ],
        'percentage' => [
            'label' => 'Százalék',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'status' => [
            'label' => 'Státusz',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Aktív',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'Függőben',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Lejárt',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Ütemezett',
            ],
        ],
        'type' => [
            'label' => 'Típus',
        ],
        'starts_at' => [
            'label' => 'Kezdés dátuma',
        ],
        'ends_at' => [
            'label' => 'Befejezés dátuma',
        ],
        'created_at' => [
            'label' => 'Létrehozva',
        ],
        'coupon' => [
            'label' => 'Kupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Elérhetőség',
        ],
        'edit' => [
            'title' => 'Alapinformációk',
        ],
        'limitations' => [
            'label' => 'Korlátozások',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Gyűjtemények',
            'description' => 'Válaszd ki, mely gyűjteményekre legyen érvényes ez a kedvezmény.',
            'actions' => [
                'attach' => [
                    'label' => 'Gyűjtemény csatolása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'type' => [
                    'label' => 'Típus',
                    'limitation' => [
                        'label' => 'Korlátozás',
                    ],
                    'exclusion' => [
                        'label' => 'Kizárás',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Korlátozás',
                        ],
                        'exclusion' => [
                            'label' => 'Kizárás',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Vásárlók',
            'description' => 'Válaszd ki, mely vásárlókra legyen érvényes ez a kedvezmény.',
            'actions' => [
                'attach' => [
                    'label' => 'Vásárló csatolása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Márkák',
            'description' => 'Válaszd ki, mely márkákra legyen érvényes ez a kedvezmény.',
            'actions' => [
                'attach' => [
                    'label' => 'Márka csatolása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'type' => [
                    'label' => 'Típus',
                    'limitation' => [
                        'label' => 'Korlátozás',
                    ],
                    'exclusion' => [
                        'label' => 'Kizárás',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Korlátozás',
                        ],
                        'exclusion' => [
                            'label' => 'Kizárás',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Termékek',
            'description' => 'Válaszd ki, mely termékekre legyen érvényes ez a kedvezmény.',
            'actions' => [
                'attach' => [
                    'label' => 'Termék hozzáadása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'type' => [
                    'label' => 'Típus',
                    'limitation' => [
                        'label' => 'Korlátozás',
                    ],
                    'exclusion' => [
                        'label' => 'Kizárás',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Korlátozás',
                        ],
                        'exclusion' => [
                            'label' => 'Kizárás',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Jutalmak',
            'description' => 'Válaszd ki, mely termékek kapnak kedvezményt, ha a kosárban megtalálhatók és a feltételek teljesülnek.',
            'actions' => [
                'attach' => [
                    'label' => 'Jutalom hozzáadása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'type' => [
                    'label' => 'Típus',
                    'limitation' => [
                        'label' => 'Korlátozás',
                    ],
                    'exclusion' => [
                        'label' => 'Kizárás',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Korlátozás',
                        ],
                        'exclusion' => [
                            'label' => 'Kizárás',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Feltételek',
            'description' => 'Válaszd ki azokat a feltételeket, amelyek szükségesek a kedvezmény alkalmazásához.',
            'actions' => [
                'attach' => [
                    'label' => 'Feltétel hozzáadása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'type' => [
                    'label' => 'Típus',
                    'limitation' => [
                        'label' => 'Korlátozás',
                    ],
                    'exclusion' => [
                        'label' => 'Kizárás',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Korlátozás',
                        ],
                        'exclusion' => [
                            'label' => 'Kizárás',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Termékváltozatok',
            'description' => 'Válaszd ki, mely termékváltozatokra legyen érvényes ez a kedvezmény.',
            'actions' => [
                'attach' => [
                    'label' => 'Termékváltozat hozzáadása',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Név',
                ],
                'sku' => [
                    'label' => 'Cikkszám (SKU)',
                ],
                'values' => [
                    'label' => 'Opció(k)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Korlátozás',
                        ],
                        'exclusion' => [
                            'label' => 'Kizárás',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
