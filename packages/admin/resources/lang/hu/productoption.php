<?php

return [

    'label' => 'Termék opció',

    'plural_label' => 'Termék opciók',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'label' => [
            'label' => 'Címke',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
        'shared' => [
            'label' => 'Megosztott',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
        'label' => [
            'label' => 'Címke',
        ],
        'handle' => [
            'label' => 'Azonosító',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Termékváltozatok mentve',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Mégse',
                ],
                'save-options' => [
                    'label' => 'Opciók mentése',
                ],
                'add-shared-option' => [
                    'label' => 'Megosztott opció hozzáadása',
                    'form' => [
                        'product_option' => [
                            'label' => 'Termék opció',
                        ],
                        'no_shared_components' => [
                            'label' => 'Nincsenek elérhető megosztott opciók.',
                        ],
                        'preselect' => [
                            'label' => 'Minden érték előzetes kiválasztása alapértelmezés szerint.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Opció hozzáadása',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Nincsenek konfigurált termék opciók',
                    'description' => 'Adj hozzá egy megosztott vagy korlátozott termék opciót a változatok generálásának megkezdéséhez.',
                ],
            ],
            'options-table' => [
                'title' => 'Termék opciók',
                'configure-options' => [
                    'label' => 'Opciók konfigurálása',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Opció',
                    ],
                    'values' => [
                        'label' => 'Értékek',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Termékváltozatok',
                'actions' => [
                    'create' => [
                        'label' => 'Termékváltozat létrehozása',
                    ],
                    'edit' => [
                        'label' => 'Szerkesztés',
                    ],
                    'delete' => [
                        'label' => 'Törlés',
                    ],
                ],
                'empty' => [
                    'heading' => 'Nincsenek konfigurált változatok',
                ],
                'table' => [
                    'new' => [
                        'label' => 'Új',
                    ],
                    'option' => [
                        'label' => 'Opció',
                    ],
                    'sku' => [
                        'label' => 'Cikkszám (SKU)',
                    ],
                    'price' => [
                        'label' => 'Ár',
                    ],
                    'stock' => [
                        'label' => 'Készlet',
                    ],
                ],
            ],
        ],
    ],

];
