<?php

return [
    'label' => 'Produkt',
    'plural_label' => 'Produkty',
    'tabs' => [
        'all' => 'Wszystkie',
        'published' => 'Published',
        'draft' => 'Draft',
        'archived' => 'Archived',
    ],
    'status' => [
        'availability' => [
            'customer_groups' => 'Produkt jest obecnie niedostępny dla wszystkich grup klientów.',
            'channels' => 'Produkt jest obecnie niedostępny dla wszystkich kanałów.',
            'hidden_from_guests' => 'Goście nie mogą obecnie zobaczyć ani kupić tego produktu. Domyślna grupa klientów nie jest dla niego włączona ani widoczna.',
            'no_default_customer_group' => 'Nie ustawiono domyślnej grupy klientów, więc widoczność dla gości nie może być kontrolowana w tym miejscu. Oznacz jedną grupę klientów jako domyślną, aby zarządzać dostępem gości.',
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
            'label_with_state' => 'Status: :state',
            'heading' => 'Edytuj status produktu',
        ],
        'delete' => [
            'confirm' => 'This permanently deletes the product and cannot be undone. If you want to hide it from the storefront without losing it, archive it instead.',
            'blocked' => 'This product has appeared on past orders and cannot be deleted — archive it instead so historical orders keep their reference.',
            'disabled_tooltip' => 'Products with order history can\'t be deleted. Archive instead.',
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
                'archived' => [
                    'label' => 'Archived',
                    'description' => 'This product is retired but still referenced by historical orders. Move back to Draft to revive it.',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tagi',
            'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
        ],
        'collections' => [
            'label' => 'Kolekcje',
            'select_collection' => 'Select a collection',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Dostępność',
        ],
        'edit' => [
            'title' => 'Basic Information',
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
