<?php

return [
    'label' => 'Kargo Bölgesi',
    'label_plural' => 'Kargo Bölgeleri',
    'form' => [
        'unrestricted' => [
            'content' => 'Bu kargo bölgesinin herhangi bir kısıtlaması yoktur ve ödeme sırasında tüm müşteriler için mevcut olacaktır.',
        ],
        'name' => [
            'label' => 'Ad',
        ],
        'type' => [
            'label' => 'Tür',
            'options' => [
                'unrestricted' => 'Kısıtsız',
                'countries' => 'Ülkelerle Sınırla',
                'states' => 'Eyaletler / İller ile Sınırla',
                'postcodes' => 'Posta Kodlarıyla Sınırla',
            ],
        ],
        'country' => [
            'label' => 'Ülke',
        ],
        'states' => [
            'label' => 'Eyaletler',
        ],
        'countries' => [
            'label' => 'Eyaletler',
        ],
        'postcodes' => [
            'label' => 'Posta Kodları',
            'helper' => 'Her posta kodunu yeni bir satırda listeleyin. NW* gibi joker karakterleri destekler',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'type' => [
            'label' => 'Tür',
            'options' => [
                'unrestricted' => 'Kısıtsız',
                'countries' => 'Ülkelerle Sınırla',
                'states' => 'Eyaletler / İller ile Sınırla',
                'postcodes' => 'Posta Kodlarıyla Sınırla',
            ],
        ],
    ],
];
