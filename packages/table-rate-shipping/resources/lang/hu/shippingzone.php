<?php

return [
    'label' => 'Szállítási zóna',
    'label_plural' => 'Szállítási zónák',
    'form' => [
        'unrestricted' => [
            'content' => 'Ehhez a szállítási zónához nincs korlátozás, ezért a pénztárnál minden vásárló számára elérhető lesz.',
        ],
        'name' => [
            'label' => 'Név',
        ],
        'type' => [
            'label' => 'Típus',
            'options' => [
                'unrestricted' => 'Korlátozás nélkül',
                'countries' => 'Országokra korlátozva',
                'states' => 'Államokra / megyékre korlátozva',
                'postcodes' => 'Irányítószámokra korlátozva',
            ],
        ],
        'country' => [
            'label' => 'Ország',
        ],
        'states' => [
            'label' => 'Megyék',
        ],
        'countries' => [
            'label' => 'Országok',
        ],
        'postcodes' => [
            'label' => 'Irányítószámok',
            'helper' => 'Minden irányítószámot új sorba írj. Támogatja a helyettesítő karaktereket, például: NW*',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Név',
        ],
        'type' => [
            'label' => 'Típus',
            'options' => [
                'unrestricted' => 'Korlátozás nélkül',
                'countries' => 'Országokra korlátozva',
                'states' => 'Államokra / megyékre korlátozva',
                'postcodes' => 'Irányítószámokra korlátozva',
            ],
        ],
    ],
];
