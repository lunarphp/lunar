<?php

return [
    'customer_groups' => [
        'title' => 'Vásárlói csoportok',
        'actions' => [
            'attach' => [
                'label' => 'Vásárlói csoport csatolása',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Név',
            ],
            'enabled' => [
                'label' => 'Engedélyezve',
            ],
            'starts_at' => [
                'label' => 'Kezdés dátuma',
            ],
            'ends_at' => [
                'label' => 'Befejezés dátuma',
            ],
            'visible' => [
                'label' => 'Látható',
            ],
            'purchasable' => [
                'label' => 'Megvásárolható',
            ],
        ],
        'table' => [
            'description' => 'Kapcsoljon vásárlói csoportokat a(z) :type-hoz az elérhetőség meghatározásához.',
            'name' => [
                'label' => 'Név',
            ],
            'enabled' => [
                'label' => 'Engedélyezve',
            ],
            'starts_at' => [
                'label' => 'Kezdés dátuma',
            ],
            'ends_at' => [
                'label' => 'Befejezés dátuma',
            ],
            'visible' => [
                'label' => 'Látható',
            ],
            'purchasable' => [
                'label' => 'Megvásárolható',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Csatornák',
        'actions' => [
            'attach' => [
                'label' => 'Csatorna ütemezése',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Engedélyezve',
                'helper_text_false' => 'A csatorna nem lesz engedélyezve, még ha van kezdő dátum sem.',
            ],
            'starts_at' => [
                'label' => 'Kezdés dátuma',
                'helper_text' => 'Hagyd üresen, ha bármely dátumtól elérhető legyen.',
            ],
            'ends_at' => [
                'label' => 'Befejezés dátuma',
                'helper_text' => 'Hagyd üresen, ha határozatlan ideig elérhető legyen.',
            ],
        ],
        'table' => [
            'description' => 'Állítsd be, mely csatornák engedélyezettek és ütemezd az elérhetőséget.',
            'name' => [
                'label' => 'Név',
            ],
            'enabled' => [
                'label' => 'Engedélyezve',
            ],
            'starts_at' => [
                'label' => 'Kezdés dátuma',
            ],
            'ends_at' => [
                'label' => 'Befejezés dátuma',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Média',
        'title_plural' => 'Médiák',
        'actions' => [
            'attach' => [
                'label' => 'Média csatolása',
            ],
            'create' => [
                'label' => 'Média létrehozása',
            ],
            'detach' => [
                'label' => 'Leválasztás',
            ],
            'view' => [
                'label' => 'Megtekintés',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Név',
            ],
            'media' => [
                'label' => 'Kép',
            ],
            'primary' => [
                'label' => 'Elsődleges',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Kép',
            ],
            'file' => [
                'label' => 'Fájl',
            ],
            'name' => [
                'label' => 'Név',
            ],
            'primary' => [
                'label' => 'Elsődleges',
            ],
        ],
        'all_media_attached' => 'Nincsenek elérhető termékképek csatoláshoz',
        'variant_description' => 'Csatoljon termékképeket ehhez a változathoz',
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URL-ek',
        'actions' => [
            'create' => [
                'label' => 'URL létrehozása',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Nyelv',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'URL-azonosító (slug)',
            ],
            'default' => [
                'label' => 'Alapértelmezett',
            ],
            'language' => [
                'label' => 'Nyelv',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'URL-azonosító (slug)',
            ],
            'default' => [
                'label' => 'Alapértelmezett',
            ],
            'language' => [
                'label' => 'Nyelv',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Vásárlói csoport árképzés',
        'title_plural' => 'Vásárlói csoport árképzés',
        'table' => [
            'heading' => 'Vásárlói csoport árképzés',
            'description' => 'Ár társítása vásárlói csoportokhoz a termék árának meghatározásához.',
            'empty_state' => [
                'label' => 'Nincs vásárlói csoport ár.',
                'description' => 'Hozz létre vásárlói csoport árat a kezdéshez.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Vásárlói csoport ár hozzáadása',
                    'modal' => [
                        'heading' => 'Vásárlói csoport ár létrehozása',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Árképzés',
        'title_plural' => 'Árképzés',
        'tab_name' => 'Ár lépcsők',
        'table' => [
            'heading' => 'Ár lépcsők',
            'description' => 'Csökkentsd az árat, ha a vásárló nagyobb mennyiséget vásárol.',
            'empty_state' => [
                'label' => 'Nincsenek ár lépcsők.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Ár lépcső hozzáadása',
                ],
            ],
            'price' => [
                'label' => 'Ár',
            ],
            'customer_group' => [
                'label' => 'Vásárlói csoport',
                'placeholder' => 'Minden vásárlói csoport',
            ],
            'min_quantity' => [
                'label' => 'Minimum mennyiség',
            ],
            'currency' => [
                'label' => 'Pénznem',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Ár',
                'helper_text' => 'A vásárlási ár, kedvezmények előtt.',
            ],
            'customer_group_id' => [
                'label' => 'Vásárlói csoport',
                'placeholder' => 'Minden vásárlói csoport',
                'helper_text' => 'Válaszd ki, melyik vásárlói csoportra alkalmazd ezt az árat.',
            ],
            'min_quantity' => [
                'label' => 'Minimum mennyiség',
                'helper_text' => 'Válaszd ki a minimális mennyiséget, amelyre ez az ár érvényes lesz.',
                'validation' => [
                    'unique' => 'A vásárlói csoport és a minimum mennyiség egyedinek kell lennie.',
                ],
            ],
            'currency_id' => [
                'label' => 'Pénznem',
                'helper_text' => 'Válaszd ki az ár pénznemét.',
            ],
            'compare_price' => [
                'label' => 'Összehasonlító ár',
                'helper_text' => 'Az eredeti ár vagy ajánlott fogyasztói ár, az összehasonlításhoz.',
            ],
            'basePrices' => [
                'title' => 'Árak',
                'form' => [
                    'price' => [
                        'label' => 'Ár',
                        'helper_text' => 'A vásárlási ár, kedvezmények előtt.',
                        'sync_price' => 'Az ár szinkronizálva van az alapértelmezett pénznemmel.',
                    ],
                    'compare_price' => [
                        'label' => 'Összehasonlító ár',
                        'helper_text' => 'Az eredeti ár vagy ajánlott fogyasztói ár, az összehasonlításhoz.',
                    ],
                ],
                'tooltip' => 'Automatikusan generálva a pénznemek közötti árfolyamok alapján.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Százalék',
            ],
            'tax_class' => [
                'label' => 'Adóosztály',
            ],
        ],
    ],
    'values' => [
        'title' => 'Értékek',
        'table' => [
            'name' => [
                'label' => 'Név',
            ],
            'position' => [
                'label' => 'Pozíció',
            ],
        ],
    ],

];
