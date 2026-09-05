<?php

return [
    'plural_label' => 'Rabaty',
    'label' => 'Rabat',
    'form' => [
        'conditions' => [
            'heading' => 'Warunki',
        ],
        'buy_x_get_y' => [
            'heading' => 'Kup X, Zdobądź Y',
        ],
        'percentage_off' => [
            'heading' => 'Rabat procentowy',
        ],
        'fixed_amount_off' => [
            'heading' => 'Rabat kwotowy',
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'starts_at' => [
            'label' => 'Data rozpoczęcia',
        ],
        'ends_at' => [
            'label' => 'Data zakończenia',
        ],
        'priority' => [
            'label' => 'Priorytet',
            'helper_text' => 'Rabaty o wyższym priorytecie będą stosowane jako pierwsze.',
        ],
        'stop' => [
            'label' => 'Zatrzymaj inne rabaty po zastosowaniu tego',
            'helper_text' => 'When this discount applies, any discount with a lower priority will be skipped. Give discounts different priorities to control the order they apply in.',
        ],
        'coupon' => [
            'label' => 'Kupon',
            'helper_text' => 'Wprowadź kupon wymagany do zastosowania rabatu. Pole puste oznacza, że zostanie on zastosowany automatycznie.',
        ],
        'max_uses' => [
            'label' => 'Maksymalna liczba użyć',
            'helper_text' => 'Pozostaw puste dla nieograniczonej liczby użyć.',
        ],
        'max_uses_per_user' => [
            'label' => 'Maksymalna liczba użyć na użytkownika',
            'helper_text' => 'Pozostaw puste dla nieograniczonej liczby użyć.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Minimalna kwota koszyka',
        ],
        'min_qty' => [
            'label' => 'Minimalna ilość produktów',
            'helper_text' => 'Minimalna ilość produktów, które muszą być w koszyku, aby rabat mógł zostać zastosowany.',
        ],
        'reward_qty' => [
            'label' => 'Ilość zrabatowanych produktów',
            'helper_text' => 'Ile sztuk każdego produktu objętych zniżką.',
        ],
        'max_reward_qty' => [
            'label' => 'Maksymalna ilość zrabatowanych produktów',
            'helper_text' => 'Maksymalna ilość sztuk każdego produktu, która może być objęta zniżką.',
        ],
        'automatic_rewards' => [
            'label' => 'Automatically add rewards',
            'helper_text' => 'Switch on to add reward products when not present in the basket.',
        ],
        'percentage' => [
            'label' => 'Procent',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'status' => [
            'label' => 'Status',
            'active' => [
                'label' => 'Aktywny',
            ],
            'pending' => [
                'label' => 'Oczekujący',
            ],
            'expired' => [
                'label' => 'Wygasły',
            ],
            'scheduled' => [
                'label' => 'Zaplanowany',
            ],
        ],
        'type' => [
            'label' => 'Typ',
        ],
        'starts_at' => [
            'label' => 'Data rozpoczęcia',
        ],
        'ends_at' => [
            'label' => 'Data zakończenia',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Dostępność',
        ],
        'edit' => [
            'title' => 'Informacje podstawowe',
        ],
        'limitations' => [
            'label' => 'Ograniczenia',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Kolekcje',
            'description' => 'Wybierz, do których kolekcji ma być ograniczony ten rabat.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj kolekcję',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Ograniczenie',
                    ],
                    'exclusion' => [
                        'label' => 'Wykluczenie',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograniczenie',
                        ],
                        'exclusion' => [
                            'label' => 'Wykluczenie',
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
            'title' => 'Marki',
            'description' => 'Wybierz, do których marek ma być ograniczony ten rabat.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj markę',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Ograniczenie',
                    ],
                    'exclusion' => [
                        'label' => 'Wykluczenie',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograniczenie',
                        ],
                        'exclusion' => [
                            'label' => 'Wykluczenie',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Produkty',
            'description' => 'Wybierz, do których produktów ma być ograniczony ten rabat.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj produkt',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Ograniczenie',
                    ],
                    'exclusion' => [
                        'label' => 'Wykluczenie',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograniczenie',
                        ],
                        'exclusion' => [
                            'label' => 'Wykluczenie',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Produkty zniżkowane',
            'description' => 'Wybierz produkty, które będą zniżkowane.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj produkt',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Ograniczenie',
                    ],
                    'exclusion' => [
                        'label' => 'Wykluczenie',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograniczenie',
                        ],
                        'exclusion' => [
                            'label' => 'Wykluczenie',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Produkty wymagane',
            'description' => 'Wybierz produkty wymagane do zastosowania rabatu.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj produkt',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Ograniczenie',
                    ],
                    'exclusion' => [
                        'label' => 'Wykluczenie',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograniczenie',
                        ],
                        'exclusion' => [
                            'label' => 'Wykluczenie',
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
            'title' => 'Warianty produktów',
            'description' => 'Wybierz, do których wariantów produktów ma być ograniczony ten rabat.',
            'actions' => [
                'attach' => [
                    'label' => 'Dodaj wariant',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Nazwa',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Opcje',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Ograniczenie',
                        ],
                        'exclusion' => [
                            'label' => 'Wykluczenie',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
