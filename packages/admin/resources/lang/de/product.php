<?php

return [

    'label' => 'Produkt',

    'plural_label' => 'Produkte',

    'status' => [
        'unpublished' => [
            'content' => 'Derzeit im Entwurfsstatus, dieses Produkt ist in allen Kanälen und Kundengruppen verborgen.',
        ],
        'availability' => [
            'customer_groups' => 'Dieses Produkt ist derzeit für alle Kundengruppen nicht verfügbar.',
            'channels' => 'Dieses Produkt ist derzeit für alle Kanäle nicht verfügbar.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
                'deleted' => 'Gelöscht',
                'draft' => 'Entwurf',
                'published' => 'Veröffentlicht',
            ],
        ],
        'name' => [
            'label' => 'Name',
        ],
        'brand' => [
            'label' => 'Marke',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Bestand',
        ],
        'producttype' => [
            'label' => 'Produkttyp',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Status aktualisieren',
            'heading' => 'Status aktualisieren',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'brand' => [
            'label' => 'Marke',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Produkttyp',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Veröffentlicht',
                    'description' => 'Dieses Produkt wird in allen aktivierten Kundengruppen und Kanälen verfügbar sein',
                ],
                'draft' => [
                    'label' => 'Entwurf',
                    'description' => 'Dieses Produkt wird in allen Kanälen und Kundengruppen verborgen sein',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'collections' => [
            'label' => 'Sammlungen',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Verfügbarkeit',
        ],
        'media' => [
            'label' => 'Medien',
        ],
        'identifiers' => [
            'label' => 'Produktkennungen',
        ],
        'inventory' => [
            'label' => 'Inventar',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Steuerklasse',
                ],
                'tax_ref' => [
                    'label' => 'Steuerreferenz',
                    'helper_text' => 'Optional, zur Integration mit Drittsystemen.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Versand',
        ],
        'variants' => [
            'label' => 'Varianten',
        ],
        'collections' => [
            'label' => 'Sammlungen',
        ],
        'associations' => [
            'label' => 'Produktverknüpfungen',
        ],
    ],

];
