<?php

return [
    'customer_groups' => [
        'title' => 'Grupy klientów',
        'actions' => [
            'attach' => [
                'label' => 'Dodaj grupę klientów',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Nazwa',
            ],
            'enabled' => [
                'label' => 'Włączona',
            ],
            'starts_at' => [
                'label' => 'Data rozpoczęcia',
            ],
            'ends_at' => [
                'label' => 'Data zakończenia',
            ],
            'visible' => [
                'label' => 'Widoczna',
            ],
            'purchasable' => [
                'label' => 'Możliwość zakupu',
            ],
        ],
        'table' => [
            'description' => 'Określ, które grupy klientów są włączone i zaplanuj dostępność.',
            'name' => [
                'label' => 'Nazwa',
                'default_description' => 'Domyślna — kontroluje dostęp gości',
            ],
            'enabled' => [
                'label' => 'Włączona',
            ],
            'starts_at' => [
                'label' => 'Data rozpoczęcia',
            ],
            'ends_at' => [
                'label' => 'Data zakończenia',
            ],
            'visible' => [
                'label' => 'Widoczna',
            ],
            'purchasable' => [
                'label' => 'Możliwość zakupu',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Kanały',
        'actions' => [
            'attach' => [
                'label' => 'Zaplanuj kolejny kanał',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Włączony',
                'helper_text_false' => 'Ten kanał nie będzie włączony, nawet jeśli jest obecna data rozpoczęcia.',
            ],
            'starts_at' => [
                'label' => 'Data rozpoczęcia',
                'helper_text' => 'Pozostaw puste, aby była dostępna na czas nieokreślony.',
            ],
            'ends_at' => [
                'label' => 'Data zakończenia',
                'helper_text' => 'Pozostaw puste, aby była dostępna na czas nieokreślony.',
            ],
        ],
        'table' => [
            'description' => 'Określ, które kanały są włączone i zaplanuj dostępność.',
            'name' => [
                'label' => 'Nazwa',
            ],
            'enabled' => [
                'label' => 'Włączony',
            ],
            'starts_at' => [
                'label' => 'Data rozpoczęcia',
            ],
            'ends_at' => [
                'label' => 'Data zakończenia',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Media',
        'title_plural' => 'Media',
        'actions' => [
            'attach' => [
                'label' => 'Attach Media',
            ],
            'create' => [
                'label' => 'Create Media',
            ],
            'detach' => [
                'label' => 'Detach',
            ],
            'view' => [
                'label' => 'View',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Name',
            ],
            'media' => [
                'label' => 'Image',
            ],
            'primary' => [
                'label' => 'Primary',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Image',
            ],
            'file' => [
                'label' => 'File',
            ],
            'name' => [
                'label' => 'Name',
            ],
            'primary' => [
                'label' => 'Primary',
            ],
        ],
        'all_media_attached' => 'There are no product images available to attach',
        'variant_description' => 'Attach product images to this variant',
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLe',
        'actions' => [
            'create' => [
                'label' => 'Utwórz URL',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Język',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Domyślny',
            ],
            'language' => [
                'label' => 'Język',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Domyślny',
            ],
            'language' => [
                'label' => 'Język',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Ceny grup klientów',
        'title_plural' => 'Ceny grup klientów',
        'table' => [
            'heading' => 'Ceny grup klientów',
            'description' => 'Określ ceny dla poszczególnych grup klientów.',
            'empty_state' => [
                'label' => 'Nie istnieją ceny grup klientów.',
                'description' => 'Dodaj ceny dla poszczególnych grup klientów.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Dodaj cenę grupy klientów',
                    'modal' => [
                        'heading' => 'Dodaj cenę grupy klientów',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Cena',
        'title_plural' => 'Ceny',
        'tab_name' => 'Progi cenowe',
        'table' => [
            'heading' => 'Progi cenowe',
            'description' => 'Zmniejsz cenę, gdy klient kupuje większe ilości.',
            'empty_state' => [
                'label' => 'Nie istnieją progi cenowe.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Dodaj próg cenowy',
                ],
            ],
            'price' => [
                'label' => 'Cena',
            ],
            'customer_group' => [
                'label' => 'Grupa klientów',
                'placeholder' => 'Wszystkie grupy klientów',
            ],
            'min_quantity' => [
                'label' => 'Minimalna ilość',
            ],
            'currency' => [
                'label' => 'Waluta',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Cena',
                'helper_text' => 'Cena zakupu, przed rabatami.',
            ],
            'customer_group_id' => [
                'label' => 'Grupa klientów',
                'placeholder' => 'Wszystkie grupy klientów',
                'helper_text' => 'Wybierz grupę klientów dla tej ceny.',
            ],
            'min_quantity' => [
                'label' => 'Minimalna ilość',
                'helper_text' => 'Wybierz minimalną ilość, dla której ta cena będzie dostępna.',
                'validation' => [
                    'unique' => 'Grupa klientów i minimalna ilość muszą być unikalne.',
                ],
            ],
            'currency_id' => [
                'label' => 'Waluta',
                'helper_text' => 'Wybierz walutę dla tej ceny.',
            ],
            'list_price' => [
                'label' => 'Cena porównawcza',
                'helper_text' => 'Oryginalna cena lub sugerowana cena producenta, do porównania z ceną zakupu.',
            ],
            'basePrices' => [
                'title' => 'Ceny bazowe',
                'form' => [
                    'price' => [
                        'label' => 'Cena',
                        'helper_text' => 'Cena zakupu, przed rabatami.',
                        'sync_price' => 'Price is synced with the default currency.',
                    ],
                    'list_price' => [
                        'label' => 'Cena porównawcza',
                        'helper_text' => 'Oryginalna cena lub sugerowana cena producenta, do porównania z ceną zakupu.',
                    ],
                ],
                'tooltip' => 'Automatycznie generowane na podstawie kursów wymiany walut.',
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
                'label' => 'Klasa podatkowa',
            ],
        ],
    ],
    'values' => [
        'title' => 'Wartości',
        'form' => [
            'name' => [
                'label' => 'Nazwa',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Nazwa',
            ],
            'position' => [
                'label' => 'Pozycja',
            ],
        ],
    ],
];
