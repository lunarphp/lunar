<?php

return [
    'label' => 'Zonă de livrare',
    'label_plural' => 'Zone de livrare',
    'form' => [
        'unrestricted' => [
            'content' => 'Această zonă de livrare nu are restricții și va fi disponibilă pentru toți clienții la finalizarea comenzii.',
        ],
        'name' => [
            'label' => 'Nume',
        ],
        'type' => [
            'label' => 'Tip',
            'options' => [
                'unrestricted' => 'Fără restricții',
                'countries' => 'Limitat la țări',
                'states' => 'Limitat la state / provincii',
                'postcodes' => 'Limitat la coduri poștale',
            ],
        ],
        'country' => [
            'label' => 'Țară',
        ],
        'states' => [
            'label' => 'Județe',
        ],
        'countries' => [
            'label' => 'Țări',
        ],
        'postcodes' => [
            'label' => 'Coduri poștale',
            'helper' => 'Listați fiecare cod poștal pe o linie separată. Suportă wildcard-uri precum NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Nume',
        ],
        'type' => [
            'label' => 'Tip',
            'options' => [
                'unrestricted' => 'Fără restricții',
                'countries' => 'Limitat la țări',
                'states' => 'Limitat la state / provincii',
                'postcodes' => 'Limitat la coduri poștale',
            ],
        ],
    ],
];
