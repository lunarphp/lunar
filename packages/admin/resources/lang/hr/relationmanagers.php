<?php

return [
    'customer_groups' => [
        'title' => 'Grupe kupaca',
        'actions' => [
            'attach' => [
                'label' => 'Dodaj grupu kupaca',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Naziv',
            ],
            'enabled' => [
                'label' => 'Omogućeno',
            ],
            'starts_at' => [
                'label' => 'Datum početka',
            ],
            'ends_at' => [
                'label' => 'Datum završetka',
            ],
            'visible' => [
                'label' => 'Vidljivo',
            ],
            'purchasable' => [
                'label' => 'Dostupno za kupnju',
            ],
        ],
        'table' => [
            'description' => 'Povežite grupe kupaca s ovim proizvodom kako biste odredili dostupnost.',
            'name' => [
                'label' => 'Naziv',
                'default_description' => 'Zadano — upravlja pristupom gostiju',
            ],
            'enabled' => [
                'label' => 'Omogućeno',
            ],
            'starts_at' => [
                'label' => 'Datum početka',
            ],
            'ends_at' => [
                'label' => 'Datum završetka',
            ],
            'visible' => [
                'label' => 'Vidljivo',
            ],
            'purchasable' => [
                'label' => 'Dostupno za kupnju',
            ],
        ],
    ],
    'channels' => [
        'actions' => [
            'attach' => [
                'label' => 'Zakaži dodatni kanal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Omogućeno',
                'helper_text_false' => 'Ovaj kanal neće biti aktiviran, čak i ako postoji datum početka.',
            ],
            'starts_at' => [
                'label' => 'Datum početka',
                'helper_text' => 'Ostavite prazno za dostupnost od bilo kojeg datuma.',
            ],
            'ends_at' => [
                'label' => 'Datum završetka',
                'helper_text' => 'Ostavite prazno za neograničenu dostupnost.',
            ],
        ],
        'table' => [
            'description' => 'Odredite koji su kanali omogućeni i zakažite dostupnost.',
            'name' => [
                'label' => 'Naziv',
            ],
            'enabled' => [
                'label' => 'Omogućeno',
            ],
            'starts_at' => [
                'label' => 'Datum početka',
            ],
            'ends_at' => [
                'label' => 'Datum završetka',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URL-ovi',
        'actions' => [
            'create' => [
                'label' => 'Stvori URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Jezik',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Zadano',
            ],
            'language' => [
                'label' => 'Jezik',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Zadano',
            ],
            'language' => [
                'label' => 'Jezik',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Cijene za grupe kupaca',
        'title_plural' => 'Cijene za grupe kupaca',
        'table' => [
            'heading' => 'Cijene za grupe kupaca',
            'description' => 'Dodijelite cijene grupama kupaca kako biste odredili cijenu proizvoda.',
            'empty_state' => [
                'label' => 'Nema cijena za grupe kupaca.',
                'description' => 'Stvorite cijenu za grupu kupaca da biste započeli.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Dodaj cijenu za grupu kupaca',
                    'modal' => [
                        'heading' => 'Stvori cijenu za grupu kupaca',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Određivanje cijena',
        'title_plural' => 'Određivanje cijena',
        'tab_name' => 'Cjenovni razredi',
        'table' => [
            'heading' => 'Cjenovni razredi',
            'description' => 'Smanjite cijenu kada kupac kupuje u većim količinama.',
            'empty_state' => [
                'label' => 'Nema cjenovnih razreda.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Dodaj cjenovni razred',
                ],
            ],
            'price' => [
                'label' => 'Cijena',
            ],
            'customer_group' => [
                'label' => 'Grupa kupaca',
                'placeholder' => 'Sve grupe kupaca',
            ],
            'min_quantity' => [
                'label' => 'Minimalna količina',
            ],
            'currency' => [
                'label' => 'Valuta',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Cijena',
                'helper_text' => 'Kupovna cijena, prije popusta.',
            ],
            'customer_group_id' => [
                'label' => 'Grupa kupaca',
                'placeholder' => 'Sve grupe kupaca',
                'helper_text' => 'Odaberite grupu kupaca na koju će se primijeniti ova cijena.',
            ],
            'min_quantity' => [
                'label' => 'Minimalna količina',
                'helper_text' => 'Odaberite minimalnu količinu za koju je ova cijena dostupna.',
                'validation' => [
                    'unique' => 'Grupa kupaca i minimalna količina moraju biti jedinstveni.',
                ],
            ],
            'currency_id' => [
                'label' => 'Valuta',
                'helper_text' => 'Odaberite valutu za ovu cijenu.',
            ],
            'compare_price' => [
                'label' => 'Usporedna cijena',
                'helper_text' => 'Izvorna cijena ili preporučena maloprodajna cijena, za usporedbu s kupovnom cijenom.',
            ],
            'basePrices' => [
                'title' => 'Cijene',
                'form' => [
                    'price' => [
                        'label' => 'Cijena',
                        'helper_text' => 'Kupovna cijena, prije popusta.',
                    ],
                    'compare_price' => [
                        'label' => 'Usporedna cijena',
                        'helper_text' => 'Izvorna cijena ili preporučena maloprodajna cijena, za usporedbu s kupovnom cijenom.',
                    ],
                ],
                'tooltip' => 'Automatski generirano na temelju tečajeva.',
            ],
        ],
    ],
    'values' => [
        'title' => 'Vrijednosti',
        'form' => [
            'name' => [
                'label' => 'Naziv',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Naziv',
            ],
            'position' => [
                'label' => 'Pozicija',
            ],
        ],
    ],
];
