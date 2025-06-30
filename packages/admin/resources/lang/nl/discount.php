<?php

return [
    'plural_label' => 'Kortingen',
    'label' => 'Korting',
    'form' => [
        'conditions' => [
            'heading' => 'Voorwaarden',
        ],
        'buy_x_get_y' => [
            'heading' => 'Koop X Krijg Y',
        ],
        'amount_off' => [
            'heading' => 'Bedrag Korting',
        ],
        'name' => [
            'label' => 'Naam',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Einddatum',
        ],
        'priority' => [
            'label' => 'Prioriteit',
            'helper_text' => 'Kortingen met een hogere prioriteit worden eerst toegepast.',
            'options' => [
                'low' => [
                    'label' => 'Laag',
                ],
                'medium' => [
                    'label' => 'Middel',
                ],
                'high' => [
                    'label' => 'Hoog',
                ],
            ],
        ],
        'stop' => [
            'label' => 'Stop andere kortingen na deze toe te passen',
        ],
        'coupon' => [
            'label' => 'Coupon',
            'helper_text' => 'Voer de vereiste coupon in voor de korting, als deze leeg is, wordt deze automatisch toegepast.',
        ],
        'max_uses' => [
            'label' => 'Maximaal gebruik',
            'helper_text' => 'Laat leeg voor onbeperkt gebruik.',
        ],
        'max_uses_per_user' => [
            'label' => 'Maximaal gebruik per gebruiker',
            'helper_text' => 'Laat leeg voor onbeperkt gebruik.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Minimale Winkelwagenbedrag',
        ],
        'min_qty' => [
            'label' => 'Producthoeveelheid',
            'helper_text' => 'Stel in hoeveel kwalificerende producten nodig zijn voor de korting.',
        ],
        'reward_qty' => [
            'label' => 'Aantal gratis items',
            'helper_text' => 'Hoeveel van elk item worden afgeprijsd.',
        ],
        'max_reward_qty' => [
            'label' => 'Maximale beloningshoeveelheid',
            'helper_text' => 'Het maximale aantal producten dat kan worden afgeprijsd, ongeacht de criteria.',
        ],
        'automatic_rewards' => [
            'label' => 'Automatisch beloningen toevoegen',
            'helper_text' => 'Schakel in om beloningsproducten toe te voegen wanneer deze niet in de winkelwagen aanwezig zijn.',
        ],
        'fixed_value' => [
            'label' => 'Vaste waarde',
        ],
        'percentage' => [
            'label' => 'Percentage',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Naam',
        ],
        'status' => [
            'label' => 'Status',
            \Lunar\Models\Discount::ACTIVE => [
                'label' => 'Actief',
            ],
            \Lunar\Models\Discount::PENDING => [
                'label' => 'In afwachting',
            ],
            \Lunar\Models\Discount::EXPIRED => [
                'label' => 'Verlopen',
            ],
            \Lunar\Models\Discount::SCHEDULED => [
                'label' => 'Gepland',
            ],
        ],
        'type' => [
            'label' => 'Type',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Einddatum',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Beschikbaarheid',
        ],
        'edit' => [
            'title' => 'Basisinformatie',
        ],
        'limitations' => [
            'label' => 'Beperkingen',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Collecties',
            'description' => 'Selecteer welke collecties beperkt moeten worden tot deze korting.',
            'actions' => [
                'attach' => [
                    'label' => 'Collectie Toevoegen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Beperking',
                    ],
                    'exclusion' => [
                        'label' => 'Uitsluiting',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beperking',
                        ],
                        'exclusion' => [
                            'label' => 'Uitsluiting',
                        ],
                    ],
                ],
            ],
        ],
        'brands' => [
            'title' => 'Merken',
            'description' => 'Selecteer welke merken beperkt moeten worden tot deze korting.',
            'actions' => [
                'attach' => [
                    'label' => 'Merk Toevoegen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Beperking',
                    ],
                    'exclusion' => [
                        'label' => 'Uitsluiting',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beperking',
                        ],
                        'exclusion' => [
                            'label' => 'Uitsluiting',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Producten',
            'description' => 'Selecteer welke producten beperkt moeten worden tot deze korting.',
            'actions' => [
                'attach' => [
                    'label' => 'Product Toevoegen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Beperking',
                    ],
                    'exclusion' => [
                        'label' => 'Uitsluiting',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beperking',
                        ],
                        'exclusion' => [
                            'label' => 'Uitsluiting',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Productbeloningen',
            'description' => 'Selecteer welke producten worden afgeprijsd als ze in de winkelwagen zitten en aan de bovenstaande voorwaarden voldoen.',
            'actions' => [
                'attach' => [
                    'label' => 'Product Toevoegen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Beperking',
                    ],
                    'exclusion' => [
                        'label' => 'Uitsluiting',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beperking',
                        ],
                        'exclusion' => [
                            'label' => 'Uitsluiting',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Productvoorwaarden',
            'description' => 'Selecteer de producten die nodig zijn voor de korting.',
            'actions' => [
                'attach' => [
                    'label' => 'Product Toevoegen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'type' => [
                    'label' => 'Type',
                    'limitation' => [
                        'label' => 'Beperking',
                    ],
                    'exclusion' => [
                        'label' => 'Uitsluiting',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beperking',
                        ],
                        'exclusion' => [
                            'label' => 'Uitsluiting',
                        ],
                    ],
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Productvarianten',
            'description' => 'Selecteer welke productvarianten beperkt moeten worden tot deze korting.',
            'actions' => [
                'attach' => [
                    'label' => 'Productvariant Toevoegen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Naam',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Optie(s)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beperking',
                        ],
                        'exclusion' => [
                            'label' => 'Uitsluiting',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
