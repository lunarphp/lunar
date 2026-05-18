<?php

return [

    'label' => 'Kolekcija',

    'plural_label' => 'Kolekcije',

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
    ],

    'pages' => [
        'children' => [
            'label' => 'Podređene kolekcije',
            'actions' => [
                'create_child' => [
                    'label' => 'Stvori podređenu kolekciju',
                ],
            ],
            'table' => [
                'children_count' => [
                    'label' => 'Broj podređenih',
                ],
                'name' => [
                    'label' => 'Naziv',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Osnovni podaci',
        ],
        'media' => [
            'label' => 'Mediji',
        ],
        'products' => [
            'label' => 'Proizvodi',
            'actions' => [
                'attach' => [
                    'label' => 'Pridruži proizvod',
                ],
            ],
        ],
    ],

];
