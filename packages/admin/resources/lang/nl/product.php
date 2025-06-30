<?php

return [

    'label' => 'Product',

    'plural_label' => 'Producten',

    'tabs' => [
        'all' => 'Allemaal',
    ],

    'status' => [
        'unpublished' => [
            'content' => 'Momenteel in conceptstatus, dit product is verborgen op alle kanalen en klantgroepen.',
        ],
        'availability' => [
            'customer_groups' => 'Dit product is momenteel niet beschikbaar voor alle klantgroepen.',
            'channels' => 'Dit product is momenteel niet beschikbaar voor alle kanalen.',
        ],
    ],

    'table' => [
        'status' => [
            'label' => 'Status',
            'states' => [
                'deleted' => 'Verwijderd',
                'draft' => 'Concept',
                'published' => 'Gepubliceerd',
            ],
        ],
        'name' => [
            'label' => 'Naam',
        ],
        'brand' => [
            'label' => 'Merk',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'stock' => [
            'label' => 'Voorraad',
        ],
        'producttype' => [
            'label' => 'Producttype',
        ],
    ],

    'actions' => [
        'edit_status' => [
            'label' => 'Status Bijwerken',
            'heading' => 'Status Bijwerken',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Naam',
        ],
        'brand' => [
            'label' => 'Merk',
        ],
        'sku' => [
            'label' => 'SKU',
        ],
        'producttype' => [
            'label' => 'Producttype',
        ],
        'status' => [
            'label' => 'Status',
            'options' => [
                'published' => [
                    'label' => 'Gepubliceerd',
                    'description' => 'Dit product zal beschikbaar zijn voor alle ingeschakelde klantgroepen en kanalen',
                ],
                'draft' => [
                    'label' => 'Concept',
                    'description' => 'Dit product zal verborgen zijn op alle kanalen en klantgroepen',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tags',
        ],
        'collections' => [
            'label' => 'Collecties',
        ],
    ],

    'pages' => [
        'availability' => [
            'label' => 'Beschikbaarheid',
        ],
        'identifiers' => [
            'label' => 'Product Identificatoren',
        ],
        'inventory' => [
            'label' => 'Voorraad',
        ],
        'pricing' => [
            'form' => [
                'tax_class_id' => [
                    'label' => 'Belastingklasse',
                ],
                'tax_ref' => [
                    'label' => 'Belastingreferentie',
                    'helper_text' => 'Optioneel, voor integratie met systemen van derden.',
                ],
            ],
        ],
        'shipping' => [
            'label' => 'Verzending',
        ],
        'variants' => [
            'label' => 'Varianten',
        ],
        'collections' => [
            'label' => 'Collecties',
            'select_collection' => 'Selecteer een collectie',
        ],
        'associations' => [
            'label' => 'Productassociaties',
        ],
    ],

];
