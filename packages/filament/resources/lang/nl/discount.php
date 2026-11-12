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
        'percentage_off' => [
            'heading' => 'Procentuele korting',
        ],
        'fixed_amount_off' => [
            'heading' => 'Vast kortingsbedrag',
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
        ],
        'stop' => [
            'label' => 'Stop andere kortingen na deze toe te passen',
            'helper_text' => 'When this discount applies, any discount with a lower priority will be skipped. Give discounts different priorities to control the order they apply in.',
        ],
        'redemption' => [
            'label' => 'Toepassing',
            'options' => [
                'automatic' => 'Automatisch toegepast',
                'coupon' => 'Vereist een coupon',
            ],
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
            'active' => [
                'label' => 'Actief',
            ],
            'pending' => [
                'label' => 'In afwachting',
            ],
            'expired' => [
                'label' => 'Verlopen',
            ],
            'scheduled' => [
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
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
            'count' => 'Codes: :count',
        ],
        'redemption' => [
            'label' => 'Toepassing',
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
        'coupons' => [
            'title' => 'Coupons',
            'actions' => [
                'create' => [
                    'label' => 'Coupon toevoegen',
                ],
            ],
            'table' => [
                'code' => [
                    'label' => 'Code',
                ],
                'uses' => [
                    'label' => 'Gebruik',
                ],
                'limit' => [
                    'label' => 'Limiet',
                    'unlimited' => 'Onbeperkt',
                ],
                'email' => [
                    'label' => 'E-mail',
                ],
                'batch' => [
                    'label' => 'Batch',
                ],
                'last_redeemed' => [
                    'label' => 'Laatst verzilverd',
                ],
            ],
            'form' => [
                'code' => [
                    'label' => 'Code',
                ],
                'max_uses' => [
                    'label' => 'Maximaal gebruik',
                ],
            ],
        ],
    ],
];
