<?php

return [
    'label' => 'Obszar dostawy',
    'label_plural' => 'Obszary dostawy',
    'form' => [
        'unrestricted' => [
            'content' => 'Ten obszar dostawy nie ma żadnych ograniczeń i będzie dostępny dla wszystkich klientów podczas realizacji zamówienia.',
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'type' => [
            'label' => 'Typ',
            'options' => [
                'unrestricted' => 'Bez ograniczeń',
                'countries' => 'Ogranicz do krajów',
                'states' => 'Ogranicz do stanów / województw',
                'postcodes' => 'Ogranicz do kodów pocztowych',
            ],
        ],
        'country' => [
            'label' => 'Kraj',
        ],
        'states' => [
            'label' => 'Województwa',
        ],
        'countries' => [
            'label' => 'Kraje',
        ],
        'postcodes' => [
            'label' => 'Kody pocztowe',
            'helper' => 'Wpisz każdy kod pocztowy w nowej linii. Obsługuje znaki wieloznaczne, takie jak 00-*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'type' => [
            'label' => 'Typ',
            'options' => [
                'unrestricted' => 'Bez ograniczeń',
                'countries' => 'Ogranicz do krajów',
                'states' => 'Ogranicz do stanów / województw',
                'postcodes' => 'Ogranicz do kodów pocztowych',
            ],
        ],
    ],
];
