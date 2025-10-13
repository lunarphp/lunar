<?php

return [

    'label' => 'Marcă',

    'plural_label' => 'Mărci',

    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'products_count' => [
            'label' => 'Nr. produse',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nume',
        ],
    ],

    'action' => [
        'delete' => [
            'notification' => [
                'error_protected' => 'Această marcă nu poate fi ștearsă deoarece are produse asociate.',
            ],
        ],
    ],
    'pages' => [
        'edit' => [
            'title' => 'Informații de bază',
        ],
        'products' => [
            'label' => 'Produse',
            'actions' => [
                'attach' => [
                    'label' => 'Asociază un produs',
                    'form' => [
                        'record_id' => [
                            'label' => 'Produs',
                        ],
                    ],
                    'notification' => [
                        'success' => 'Produs asociat cu marca',
                    ],
                ],
                'detach' => [
                    'notification' => [
                        'success' => 'Produs detașat.',
                    ],
                ],
            ],
        ],
        'collections' => [
            'label' => 'Colecții',
            'table' => [
                'header_actions' => [
                    'attach' => [
                        'record_select' => [
                            'placeholder' => 'Selectează o colecție',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'attach' => [
                    'label' => 'Asociază o colecție',
                ],
            ],
        ],
    ],

];
