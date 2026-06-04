<?php

return [
    'label' => 'Product',
    'plural_label' => 'Producten',
    'tabs' => [
        'all' => 'Allemaal',
        'published' => 'Published',
        'draft' => 'Draft',
        'archived' => 'Archived',
    ],
    'status' => [
        'availability' => [
            'customer_groups' => 'Dit product is momenteel niet beschikbaar voor alle klantgroepen.',
            'channels' => 'Dit product is momenteel niet beschikbaar voor alle kanalen.',
            'hidden_from_guests' => 'Gasten kunnen dit product op dit moment niet zien of kopen. De standaard klantgroep is er niet voor ingeschakeld of zichtbaar.',
            'no_default_customer_group' => 'Er is geen standaard klantgroep ingesteld, dus de zichtbaarheid voor gasten kan hier niet worden geregeld. Markeer een klantgroep als standaard om de toegang voor gasten te beheren.',
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
            'label_with_state' => 'Status: :state',
            'heading' => 'Status Bijwerken',
        ],
        'delete' => [
            'confirm' => 'This permanently deletes the product and cannot be undone. If you want to hide it from the storefront without losing it, archive it instead.',
            'blocked' => 'This product has appeared on past orders and cannot be deleted — archive it instead so historical orders keep their reference.',
            'disabled_tooltip' => 'Products with order history can\'t be deleted. Archive instead.',
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
                'archived' => [
                    'label' => 'Archived',
                    'description' => 'This product is retired but still referenced by historical orders. Move back to Draft to revive it.',
                ],
            ],
        ],
        'tags' => [
            'label' => 'Tags',
            'helper_text' => 'Separate tags by pressing Enter, Tab or comma (,)',
        ],
        'collections' => [
            'label' => 'Collecties',
            'select_collection' => 'Select a collection',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Beschikbaarheid',
        ],
        'edit' => [
            'title' => 'Basic Information',
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
