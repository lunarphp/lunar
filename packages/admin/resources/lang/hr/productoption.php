<?php

return [

    'label' => 'Opcija proizvoda',

    'plural_label' => 'Opcije proizvoda',

    'table' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'label' => [
            'label' => 'Oznaka',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
        'shared' => [
            'label' => 'Dijeljeno',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'label' => [
            'label' => 'Oznaka',
        ],
        'handle' => [
            'label' => 'Identifikator',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Varijante proizvoda spremljene',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Odustani',
                ],
                'save-options' => [
                    'label' => 'Spremi opcije',
                ],
                'add-shared-option' => [
                    'label' => 'Dodaj dijeljenu opciju',
                    'form' => [
                        'product_option' => [
                            'label' => 'Opcija proizvoda',
                        ],
                        'no_shared_components' => [
                            'label' => 'Nema dostupnih dijeljenih opcija.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Dodaj opciju',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Nije konfigurirana nijedna opcija proizvoda',
                    'description' => 'Dodajte dijeljenu ili ograničenu opciju proizvoda kako biste generirali varijante.',
                ],
            ],
            'options-table' => [
                'title' => 'Opcije proizvoda',
                'configure-options' => [
                    'label' => 'Konfiguriraj opcije',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Opcija',
                    ],
                    'values' => [
                        'label' => 'Vrijednosti',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Varijante proizvoda',
                'actions' => [
                    'create' => [
                        'label' => 'Stvori varijantu',
                    ],
                    'edit' => [
                        'label' => 'Uredi',
                    ],
                    'delete' => [
                        'label' => 'Izbriši',
                    ],
                ],
                'empty' => [
                    'heading' => 'Nije konfigurirana nijedna varijanta',
                ],
                'table' => [
                    'new' => [
                        'label' => 'NOVO',
                    ],
                    'option' => [
                        'label' => 'Opcija',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Cijena',
                    ],
                    'stock' => [
                        'label' => 'Zaliha',
                    ],
                ],
            ],
        ],
    ],

];
