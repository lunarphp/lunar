<?php

return [

    'label' => 'Marka',

    'plural_label' => 'Marki',

    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'products_count' => [
            'label' => 'Liczba produktów',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Tej marki nie można usunąć, ponieważ są z nią powiązane produkty.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Informacje podstawowe',
        ],
        'products' => [
            'label' => 'Produkty',
            'actions' => [
                'attach' => [
                    'label' => 'Dołącz produkt',
                    'form' => [
                        'record_id' => [
                            'label' => 'Produkt',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Produkt dodany do marki.',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Produkt odpięty od marki.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Kolekcje',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Wybierz kolekcję',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Powiąż kolekcję',
                ],
            ],
        ],
    ],

];
