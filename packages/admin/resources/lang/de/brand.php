<?php

return [

    'label' => 'Marke',

    'plural_label' => 'Marken',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'products_count' => [
            'label' => 'Anzahl Produkte',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Diese Marke kann nicht gelöscht werden, da damit verbundene Produkte vorhanden sind.',
            ],
        ],
    ],
    'pages' => [
        'products' => [
            'label' => 'Produkte',
            'actions' => [
                'attach' => [
                    'label' => 'Ein Produkt zuordnen',
                    'form' => [
                        'record_id' => [
                            'label' => 'Produkt',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Produkt der Marke zugeordnet',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Produkt entfernt.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Sammlungen',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Wählen Sie eine Sammlung aus',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Eine Sammlung zuordnen',
                ],
            ],
        ],
    ],

];
