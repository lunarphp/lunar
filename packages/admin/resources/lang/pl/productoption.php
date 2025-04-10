<?php

return [

    'label' => 'Opcja produktu',

    'plural_label' => 'Opcje produktu',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'label' => [
            'label' => 'Etykieta',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
        'shared' => [
            'label' => 'Współdzielony',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'label' => [
            'label' => 'Etykieta',
        ],
        'handle' => [
            'label' => 'Slug',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Warianty zapisane',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Anuluj',
                ],
                'save-options' => [
                    'label' => 'Zapisz opcje',
                ],
                'add-shared-option' => [
                    'label' => 'Dodaj opcję współdzieloną',
                    'form' => [
                        'product_option' => [
                            'label' => 'Opcja produktu',
                        ],
                        'no_shared_components' => [
                            'label' => 'Brak opcji współdzielonych',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Dodaj opcję',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Brak opcji produktu',
                    'description' => 'Dodaj opcję produktu współdzieloną lub ograniczoną, aby rozpocząć generowanie wariantów.',
                ],
            ],
            'options-table' => [
                'title' => 'Opcje produktu',
                'configure-options' => [
                    'label' => 'Konfiguruj opcje',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Opcja',
                    ],
                    'values' => [
                        'label' => 'Wartości',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Warianty',
                'actions' => [
                    'create' => [
                        'label' => 'Utwórz wariant',
                    ],
                    'edit' => [
                        'label' => 'Edytuj',
                    ],
                    'delete' => [
                        'label' => 'Usuń',
                    ],
                ],
                'empty' => [
                    'heading' => 'Brak wariantów',
                ],
                'table' => [
                    'new' => [
                        'label' => 'Nowy',
                    ],
                    'option' => [
                        'label' => 'Opcja',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Cena',
                    ],
                    'stock' => [
                        'label' => 'Stan magazynowy',
                    ],
                ],
            ],
        ],
    ],

];
