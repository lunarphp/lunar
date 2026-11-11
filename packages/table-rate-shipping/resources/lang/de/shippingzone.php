<?php

return [
    'label' => 'Versandzone',
    'label_plural' => 'Versandzonen',
    'form' => [
        'unrestricted' => [
            'content' => 'Diese Versandzone hat keine Einschränkungen und steht allen Kunden an der Kasse zur Verfügung.',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'type' => [
            'label' => 'Typ',
            'options' => [
                'unrestricted' => 'Uneingeschränkt',
                'countries' => 'Auf Länder beschränken',
                'states' => 'Auf Staaten / Provinzen beschränken',
                'postcodes' => 'Auf Postleitzahlen beschränken',
            ],
        ],
        'country' => [
            'label' => 'Land',
        ],
        'states' => [
            'label' => 'Staaten',
        ],
        'countries' => [
            'label' => 'Länder',
        ],
        'postcodes' => [
            'label' => 'Postleitzahlen',
            'helper' => 'Listen Sie jede Postleitzahl in einer neuen Zeile auf. Unterstützt Platzhalter wie NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'type' => [
            'label' => 'Typ',
            'options' => [
                'unrestricted' => 'Uneingeschränkt',
                'countries' => 'Auf Länder beschränken',
                'states' => 'Auf Staaten / Provinzen beschränken',
                'postcodes' => 'Auf Postleitzahlen beschränken',
            ],
        ],
    ],
];
