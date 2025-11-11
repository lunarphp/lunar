<?php

return [

    'label' => 'Vergi Bölgesi',

    'plural_label' => 'Vergi Bölgeleri',

    'table' => [
        'name' => [
            'label' => 'Ad',
        ],
        'zone_type' => [
            'label' => 'Bölge Türü',
        ],
        'active' => [
            'label' => 'Aktif',
        ],
        'default' => [
            'label' => 'Varsayılan',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Ad',
        ],
        'zone_type' => [
            'label' => 'Bölge Türü',
            'options' => [
                'country' => 'Ülkelerle Sınırla',
                'states' => 'Eyaletlerle Sınırla',
                'postcodes' => 'Posta Kodlarıyla Sınırla',
            ],
        ],
        'price_display' => [
            'label' => 'Fiyat Gösterimi',
            'options' => [
                'include_tax' => 'Vergi Dahil',
                'exclude_tax' => 'Vergi Hariç',
            ],
        ],
        'active' => [
            'label' => 'Aktif',
        ],
        'default' => [
            'label' => 'Varsayılan',
        ],

        'zone_countries' => [
            'label' => 'Ülkeler',
        ],

        'zone_country' => [
            'label' => 'Ülke',
        ],

        'zone_states' => [
            'label' => 'Eyaletler',
        ],

        'zone_postcodes' => [
            'label' => 'Posta Kodları',
            'helper' => 'Her posta kodunu yeni bir satırda listeleyin. NW* gibi joker karakterleri destekler',
        ],

    ],

];
