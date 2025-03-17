<?php

return [
    'shipping_methods' => [
        'customer_groups' => [
            'description' => 'Przypisz grupy klientów do tej metody dostawy, aby określić jej dostępność.',
        ],
    ],
    'shipping_rates' => [
        'title_plural' => 'Stawki dostawy',
        'actions' => [
            'create' => [
                'label' => 'Utwórz stawkę dostawy',
            ],
        ],
        'notices' => [
            'prices_incl_tax' => 'Wszystkie ceny zawierają podatek, który będzie brany pod uwagę przy obliczaniu minimalnej kwoty.',
            'prices_excl_tax' => 'Wszystkie ceny nie zawierają podatku, minimalna kwota będzie obliczana na podstawie wartości koszyka.',
        ],
        'form' => [
            'shipping_method_id' => [
                'label' => 'Metoda dostawy',
            ],
            'price' => [
                'label' => 'Cena',
            ],
            'prices' => [
                'label' => 'Progi cenowe',
                'repeater' => [
                    'customer_group_id' => [
                        'label' => 'Grupa klientów',
                        'placeholder' => 'Wszyscy',
                    ],
                    'currency_id' => [
                        'label' => 'Waluta',
                    ],
                    'min_quantity' => [
                        'label' => 'Minimalna kwota',
                    ],
                    'price' => [
                        'label' => 'Cena',
                    ],
                ],
            ],
        ],
        'table' => [
            'shipping_method' => [
                'label' => 'Metoda dostawy',
            ],
            'price' => [
                'label' => 'Cena',
            ],
            'price_breaks_count' => [
                'label' => 'Progi cenowe',
            ],
        ],
    ],
    'exclusions' => [
        'title_plural' => 'Wyłączenia',
        'form' => [
            'purchasable' => [
                'label' => 'Produkt',
            ],
        ],
        'actions' => [
            'create' => [
                'label' => 'Dodaj listę wyłączeń dostawy',
            ],
            'attach' => [
                'label' => 'Dodaj listę wyłączeń',
            ],
            'detach' => [
                'label' => 'Usuń',
            ],
        ],
    ],
];
