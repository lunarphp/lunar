<?php

return [
    'customer_groups' => [
        'title' => 'Klantengroepen',
        'actions' => [
            'attach' => [
                'label' => 'Klantengroep Koppelen',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Naam',
            ],
            'enabled' => [
                'label' => 'Ingeschakeld',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
            ],
            'ends_at' => [
                'label' => 'Einddatum',
            ],
            'visible' => [
                'label' => 'Zichtbaar',
            ],
            'purchasable' => [
                'label' => 'Koopbaar',
            ],
        ],
        'table' => [
            'description' => 'Koppel klantengroepen aan dit :type om de beschikbaarheid te bepalen.',
            'name' => [
                'label' => 'Naam',
            ],
            'enabled' => [
                'label' => 'Ingeschakeld',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
            ],
            'ends_at' => [
                'label' => 'Einddatum',
            ],
            'visible' => [
                'label' => 'Zichtbaar',
            ],
            'purchasable' => [
                'label' => 'Koopbaar',
            ],
        ],
    ],
    'channels' => [
        'title' => 'Kanalen',
        'actions' => [
            'attach' => [
                'label' => 'Nog een Kanaal Inplannen',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Ingeschakeld',
                'helper_text_false' => 'Dit kanaal wordt niet ingeschakeld, zelfs als er een startdatum aanwezig is.',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
                'helper_text' => 'Laat leeg om beschikbaar te zijn vanaf elke datum.',
            ],
            'ends_at' => [
                'label' => 'Einddatum',
                'helper_text' => 'Laat leeg om onbeperkt beschikbaar te zijn.',
            ],
        ],
        'table' => [
            'description' => 'Bepaal welke kanalen zijn ingeschakeld en plan de beschikbaarheid.',
            'name' => [
                'label' => 'Naam',
            ],
            'enabled' => [
                'label' => 'Ingeschakeld',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
            ],
            'ends_at' => [
                'label' => 'Einddatum',
            ],
        ],
    ],
    'medias' => [
        'title' => 'Media',
        'title_plural' => 'Media',
        'actions' => [
            'create' => [
                'label' => 'Media Aanmaken',
            ],
            'view' => [
                'label' => 'Bekijken',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Naam',
            ],
            'media' => [
                'label' => 'Afbeelding',
            ],
            'primary' => [
                'label' => 'Primair',
            ],
        ],
        'table' => [
            'image' => [
                'label' => 'Afbeelding',
            ],
            'file' => [
                'label' => 'Bestand',
            ],
            'name' => [
                'label' => 'Naam',
            ],
            'primary' => [
                'label' => 'Primair',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'URL Aanmaken',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Taal',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Standaard',
            ],
            'language' => [
                'label' => 'Taal',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Standaard',
            ],
            'language' => [
                'label' => 'Taal',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Klantengroep Prijzen',
        'title_plural' => 'Klantengroep Prijzen',
        'table' => [
            'heading' => 'Klantengroep Prijzen',
            'description' => 'Koppel prijs aan klantengroepen om de productprijs te bepalen.',
            'empty_state' => [
                'label' => 'Er bestaan geen klantengroep prijzen.',
                'description' => 'Maak een klantengroep prijs om te beginnen.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Klantengroep Prijs Toevoegen',
                    'modal' => [
                        'heading' => 'Klantengroep Prijs Aanmaken',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Prijzen',
        'title_plural' => 'Prijzen',
        'tab_name' => 'Prijsbreuken',
        'table' => [
            'heading' => 'Prijsbreuken',
            'description' => 'Verlaag de prijs wanneer een klant in grotere hoeveelheden koopt.',
            'empty_state' => [
                'label' => 'Er bestaan geen prijsbreuken.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Prijsbreuk Toevoegen',
                ],
            ],
            'price' => [
                'label' => 'Prijs',
            ],
            'customer_group' => [
                'label' => 'Klantengroep',
                'placeholder' => 'Alle Klantengroepen',
            ],
            'min_quantity' => [
                'label' => 'Minimale Hoeveelheid',
            ],
            'currency' => [
                'label' => 'Valuta',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Prijs',
                'helper_text' => 'De aankoopprijs, voor kortingen.',
            ],
            'customer_group_id' => [
                'label' => 'Klantengroep',
                'placeholder' => 'Alle Klantengroepen',
                'helper_text' => 'Selecteer welke klantengroep deze prijs van toepassing is.',
            ],
            'min_quantity' => [
                'label' => 'Minimale Hoeveelheid',
                'helper_text' => 'Selecteer de minimale hoeveelheid waarvoor deze prijs beschikbaar is.',
                'validation' => [
                    'unique' => 'Klantengroep en Minimale Hoeveelheid moeten uniek zijn.',
                ],
            ],
            'currency_id' => [
                'label' => 'Valuta',
                'helper_text' => 'Selecteer de valuta voor deze prijs.',
            ],
            'compare_price' => [
                'label' => 'Vergelijkingsprijs',
                'helper_text' => 'De oorspronkelijke prijs of adviesprijs, ter vergelijking met de aankoopprijs.',
            ],
            'basePrices' => [
                'title' => 'Prijzen',
                'form' => [
                    'price' => [
                        'label' => 'Prijs',
                        'helper_text' => 'De aankoopprijs, voor kortingen.',
                    ],
                    'compare_price' => [
                        'label' => 'Vergelijkingsprijs',
                        'helper_text' => 'De oorspronkelijke prijs of adviesprijs, ter vergelijking met de aankoopprijs.',
                    ],
                ],
                'tooltip' => 'Automatisch gegenereerd op basis van wisselkoersen.',
            ],
        ],
    ],
    'tax_rate_amounts' => [
        'table' => [
            'description' => '',
            'percentage' => [
                'label' => 'Percentage',
            ],
            'tax_class' => [
                'label' => 'Belastingklasse',
            ],
        ],
    ],
    'values' => [
        'title' => 'Waarden',
        'form' => [
            'name' => [
                'label' => 'Naam',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Naam',
            ],
            'position' => [
                'label' => 'Positie',
            ],
        ],
    ],
];
