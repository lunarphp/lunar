<?php

return [

    'label' => 'Márka',

    'plural_label' => 'Márkák',

    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'products_count' => [
            'label' => 'Termékek száma',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Név',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'A márka nem törölhető, mert termékek vannak hozzárendelve.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Alapvető információk',
        ],
        'products' => [
            'label' => 'Termékek',
            'actions' => [
                'attach' => [
                    'label' => 'Termék hozzáadása',
                    'form' => [
                        'record_id' => [
                            'label' => 'Termék',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Termék hozzárendelve a márkához',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Termék leválasztva.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Gyűjtemények',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Válassz gyűjteményt',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Gyűjtemény hozzárendelése',
                ],
            ],
        ],
    ],

];
