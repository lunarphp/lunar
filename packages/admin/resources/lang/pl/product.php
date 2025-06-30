<?php

return [

    'label' => 'Produkt',

    'plural_label' => 'Produkty',

    'tabs' => [
        'all' => 'Wszystkie',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Produkt jest obecnie w trybie szkicu i jest ukryty we wszystkich kanałach i grupach klientów.',
        ],
        'availability' => [
            'customer_groups' => 'Produkt jest obecnie niedostępny dla wszystkich grup klientów.',
            'channels' => 'Produkt jest obecnie niedostępny dla wszystkich kanałów.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
                'deleted' => 'Usunięty',
                'draft' => 'Szkic',
                'published' => 'Opublikowany',
            ],
        ],
        'name' => [
            'label' => 'Nazwa',
        ],
        'brand' => [
            'label' => 'Marka',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Stan magazynowy',
        ],
        'producttype' => [
            'label' => 'Typ produktu',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Edytuj status',
            'heading' => 'Edytuj status produktu',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Nazwa',
        ],
        'brand' => [
            'label' => 'Marka',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Typ produktu',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Opublikowany',
                    'description' => 'Ten produkt będzie widoczny we wszystkich kanałach i grupach klientów',
                ],
                'draft' => [
                    'label' => 'Szkic',
                    'description' => 'Ten produkt jest ukryty we wszystkich kanałach i grupach klientów',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tagi',
        ],
        'collections' => [
            'label' => 'Kolekcje',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Dostępność',
        ],
        'media' => [
            'label' => 'Media',
        ],
        'identifiers' => [
            'label' => 'Identyfikatory',
        ],
        'inventory' => [
            'label' => 'Stan magazynowy',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Klasa podatkowa',
                ],
                'tax_ref' => [
                    'label' => 'Numer referencyjny VAT',
                    'helper_text' => 'Opcjonalnie, do integracji z systemami zewnętrznymi.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Dostawa',
        ],
        'variants' => [
            'label' => 'Warianty',
        ],
        'collections' => [
            'label' => 'Kolekcje',
            'select_collection' => 'Wybierz kolekcję',
        ],
        'associations' => [
            'label' => 'Powiązania',
        ],
    ],

];
