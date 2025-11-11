<?php

return [
    'customer_groups' => [
        'title' => 'Grupuri de clienți',
        'actions' => [
            'attach' => [
                'label' => 'Atașează grup de clienți',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nume',
            ],
            'enabled' => [
                'label' => 'Activat',
            ],
            'starts_at' => [
                'label' => 'Data de început',
            ],
            'ends_at' => [
                'label' => 'Data de sfârșit',
            ],
            'visible' => [
                'label' => 'Vizibil',
            ],
            'purchasable' => [
                'label' => 'Achiziționabil',
            ],
        ],
        'table' => [
            'description' => 'Asociați grupuri de clienți la acest :type pentru a determina disponibilitatea.',
            'name' => [
                'label' => 'Nume',
            ],
            'enabled' => [
                'label' => 'Activat',
            ],
            'starts_at' => [
                'label' => 'Data de început',
            ],
            'ends_at' => [
                'label' => 'Data de sfârșit',
            ],
            'visible' => [
                'label' => 'Vizibil',
            ],
            'purchasable' => [
                'label' => 'Achiziționabil',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Canale',
        'actions' => [
            'attach' => [
                'label' => 'Programează alt canal',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Activat',
                'helper_text_false' => 'Acest canal nu va fi activat chiar dacă există o dată de început.',
            ],
            'starts_at' => [
                'label' => 'Data de început',
                'helper_text' => 'Lăsați gol pentru a fi disponibil de la orice dată.',
            ],
            'ends_at' => [
                'label' => 'Data de sfârșit',
                'helper_text' => 'Lăsați gol pentru a fi disponibil pe termen nelimitat.',
            ],
        ],
        'table' => [
            'description' => 'Determinați ce canale sunt activate și programați disponibilitatea.',
            'name' => [
                'label' => 'Nume',
            ],
            'enabled' => [
                'label' => 'Activat',
            ],
            'starts_at' => [
                'label' => 'Data de început',
            ],
            'ends_at' => [
                'label' => 'Data de sfârșit',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Media',
        'title_plural' => 'Media',
        'actions' => [
            'attach' => [
                'label' => 'Atașează media',
            ],
            'create' => [
                'label' => 'Creează media',
            ],
            'detach' => [
                'label' => 'Detașează',
            ],
            'view' => [
                'label' => 'Vezi',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nume',
            ],
            'media' => [
                'label' => 'Imagine',
            ],
            'primary' => [
                'label' => 'Principală',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Imagine',
            ],
            'file' => [
                'label' => 'Fișier',
            ],
            'name' => [
                'label' => 'Nume',
            ],
            'primary' => [
                'label' => 'Principală',
            ],
        ],
        'all_media_attached' => 'Nu există imagini de produs disponibile pentru atașare',
        'variant_description' => 'Atașați imaginile produsului la această variantă',
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URL-uri',
        'actions' => [
            'create' => [
                'label' => 'Creează URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Limbă',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Implicit',
            ],
            'language' => [
                'label' => 'Limbă',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Implicit',
            ],
            'language' => [
                'label' => 'Limbă',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Prețuri pe grupuri de clienți',
        'title_plural' => 'Prețuri pe grupuri de clienți',
        'table' => [
            'heading' => 'Prețuri pe grupuri de clienți',
            'description' => 'Asociați prețuri grupurilor de clienți pentru a determina prețul produsului.',
            'empty_state' => [
                'label' => 'Nu există prețuri pe grupuri de clienți.',
                'description' => 'Creați un preț pentru un grup de clienți pentru a începe.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Adaugă preț pentru grup de clienți',
                    'modal' => [
                        'heading' => 'Creează preț pentru grup de clienți',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Prețuri',
        'title_plural' => 'Prețuri',
        'tab_name' => 'Reduceri cantitative',
        'table' => [
            'heading' => 'Reduceri cantitative',
            'description' => 'Reduceți prețul când un client cumpără cantități mai mari.',
            'empty_state' => [
                'label' => 'Nu există reduceri cantitative.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Adaugă reducere cantitativă',
                ],
            ],
            'price' => [
                'label' => 'Preț',
            ],
            'customer_group' => [
                'label' => 'Grup de clienți',
                'placeholder' => 'Toate grupurile de clienți',
            ],
            'min_quantity' => [
                'label' => 'Cantitate minimă',
            ],
            'currency' => [
                'label' => 'Monedă',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Preț',
                'helper_text' => 'Prețul de achiziție, înainte de reduceri.',
            ],
            'customer_group_id' => [
                'label' => 'Grup de clienți',
                'placeholder' => 'Toate grupurile de clienți',
                'helper_text' => 'Selectați grupul de clienți pentru care se aplică acest preț.',
            ],
            'min_quantity' => [
                'label' => 'Cantitate minimă',
                'helper_text' => 'Selectați cantitatea minimă pentru care va fi disponibil acest preț.',
                'validation' => [
                    'unique' => 'Grupul de clienți și cantitatea minimă trebuie să fie unice.',
                ],
            ],
            'currency_id' => [
                'label' => 'Monedă',
                'helper_text' => 'Selectați moneda pentru acest preț.',
            ],
            'compare_price' => [
                'label' => 'Preț de comparație',
                'helper_text' => 'Prețul original sau RRP, pentru comparație cu prețul de achiziție.',
            ],
            'basePrices' => [
                'title' => 'Prețuri',
                'form' => [
                    'price' => [
                        'label' => 'Preț',
                        'helper_text' => 'Prețul de achiziție, înainte de reduceri.',
                        'sync_price' => 'Prețul este sincronizat cu moneda implicită.',
                    ],
                    'compare_price' => [
                        'label' => 'Preț de comparație',
                        'helper_text' => 'Prețul original sau RRP, pentru comparație cu prețul de achiziție.',
                    ],
                ],
                'tooltip' => 'Generat automat pe baza cursurilor de schimb valutar.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Procent',
            ],
            'tax_class' => [
                'label' => 'Clasă de taxe',
            ],
        ],
    ],
    'values' => [
        'title' => 'Valori',
        'table' => [
            'name' => [
                'label' => 'Nume',
            ],
            'position' => [
                'label' => 'Poziție',
            ],
        ],
    ],

];
