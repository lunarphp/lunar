<?php

return [

    'label' => 'Proizvod',

    'plural_label' => 'Proizvodi',

    'status' => [
        'unpublished' => [
            'content' => 'Trenutno u statusu skice, ovaj proizvod nije dostupan ni u jednom kanalu ni u jednoj grupi kupaca.',
        ],
        'availability' => [
            'customer_groups' => 'Ovaj proizvod trenutno nije dostupan nijednoj grupi kupaca.',
            'channels' => 'Ovaj proizvod trenutno nije dostupan nijednom kanalu.',
            'hidden_from_guests' => 'Gosti trenutno ne mogu vidjeti ni kupiti ovaj proizvod. Zadana grupa kupaca nije omogućena ni vidljiva za njega.',
            'no_default_customer_group' => 'Zadana grupa kupaca nije postavljena, pa se vidljivost za goste ne može kontrolirati ovdje. Označite jednu grupu kupaca kao zadanu kako biste upravljali pristupom gostiju.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
                'deleted' => 'Izbrisano',
                'draft' => 'Skica',
                'published' => 'Objavljeno',
            ],
        ],
        'name' => [
            'label' => 'Naziv',
        ],
        'brand' => [
            'label' => 'Brend',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Zaliha',
        ],
        'producttype' => [
            'label' => 'Tip proizvoda',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Ažuriraj status',
            'heading' => 'Ažuriraj status',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naziv',
        ],
        'brand' => [
            'label' => 'Brend',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Tip proizvoda',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Objavljeno',
                    'description' => 'Ovaj proizvod bit će dostupan u svim omogućenim grupama kupaca i kanalima',
                ],
                'draft' => [
                    'label' => 'Skica',
                    'description' => 'Ovaj proizvod bit će skriven u svim kanalima i grupama kupaca',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Oznake',
        ],
        'collections' => [
            'label' => 'Kolekcije',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Dostupnost',
        ],
        'media' => [
            'label' => 'Mediji',
        ],
        'identifiers' => [
            'label' => 'Identifikatori proizvoda',
        ],
        'inventory' => [
            'label' => 'Inventar',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Porezni razred',
                ],
                'tax_ref' => [
                    'label' => 'Porezna referenca',
                    'helper_text' => 'Opcionalno, za integraciju sa sustavima trećih strana.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Dostava',
        ],
        'variants' => [
            'label' => 'Varijante',
        ],
        'collections' => [
            'label' => 'Kolekcije',
            'select_collection' => 'Odaberite kolekciju',
        ],
        'associations' => [
            'label' => 'Povezani proizvodi',
        ],
    ],

];
