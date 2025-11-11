<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Asociați grupuri de clienți acestei metode de livrare pentru a-i stabili disponibilitatea.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Tarife de livrare',
        'actions' => [
            'create' => [
                'label' => 'Creează tarif de livrare',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Toate prețurile includ taxa, care va fi luată în calcul la determinarea cheltuielii minime.',
            'prices_excl_tax' => 'Toate prețurile exclud taxa; cheltuiala minimă va fi calculată pe baza subtotalului coșului.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Metodă de livrare',
            ],
            'price' => [
                'label' => 'Preț',
            ],
            'prices' => [
                'label' => 'Transe de preț',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Grup de clienți',
                        'placeholder' => 'Oricare',
                    ],
                    'currency_id' => [
                        'label' => 'Monedă',
                    ],
                    'min_spend' => [
                        'label' => 'Cheltuială min.',
                    ],
                    'min_weight' => [
                        'label' => 'Greutate min.',
                    ],
                    'price' => [
                        'label' => 'Preț',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Metodă de livrare',
            ],
            'price' => [
                'label' => 'Preț',
            ],
            'price_breaks_count' => [
                'label' => 'Transe de preț',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Excluderi de livrare',
        'form' => [
            'purchasable' => [
                'label' => 'Produs',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Adaugă listă de excluderi pentru livrare',
            ],
            'attach' => [
                'label' => 'Adaugă listă de excluderi',
            ],
            'detach' => [
                'label' => 'Elimină',
            ],
        ],
    ],
];
