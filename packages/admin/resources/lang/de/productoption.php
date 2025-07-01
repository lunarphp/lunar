<?php

return [

    'label' => 'Produktoption',

    'plural_label' => 'Produktoptionen',

    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'label' => [
            'label' => 'Bezeichnung',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'shared' => [
            'label' => 'Geteilt',
        ],
    ],

    'form' => [
        'name' => [
            'label' => 'Name',
        ],
        'label' => [
            'label' => 'Bezeichnung',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
    ],

    'widgets' => [
        'product-options' => [
            'notifications' => [
                'save-variants' => [
                    'success' => [
                        'title' => 'Produktvarianten gespeichert',
                    ],
                ],
            ],
            'actions' => [
                'cancel' => [
                    'label' => 'Abbrechen',
                ],
                'save-options' => [
                    'label' => 'Optionen speichern',
                ],
                'add-shared-option' => [
                    'label' => 'Geteilte Option hinzufügen',
                    'form' => [
                        'product_option' => [
                            'label' => 'Produktoption',
                        ],
                        'no_shared_components' => [
                            'label' => 'Keine geteilten Optionen verfügbar.',
                        ],
                    ],
                ],
                'add-restricted-option' => [
                    'label' => 'Option hinzufügen',
                ],
            ],
            'options-list' => [
                'empty' => [
                    'heading' => 'Keine Produktoptionen konfiguriert',
                    'description' => 'Fügen Sie eine geteilte oder eingeschränkte Produktoption hinzu, um Varianten zu generieren.',
                ],
            ],
            'options-table' => [
                'title' => 'Produktoptionen',
                'configure-options' => [
                    'label' => 'Optionen konfigurieren',
                ],
                'table' => [
                    'option' => [
                        'label' => 'Option',
                    ],
                    'values' => [
                        'label' => 'Werte',
                    ],
                ],
            ],
            'variants-table' => [
                'title' => 'Produktvarianten',
                'actions' => [
                    'create' => [
                        'label' => 'Variante erstellen',
                    ],
                    'edit' => [
                        'label' => 'Bearbeiten',
                    ],
                    'delete' => [
                        'label' => 'Löschen',
                    ],
                ],
                'empty' => [
                    'heading' => 'Keine Varianten konfiguriert',
                ],
                'table' => [
                    'new' => [
                        'label' => 'NEU',
                    ],
                    'option' => [
                        'label' => 'Option',
                    ],
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'price' => [
                        'label' => 'Preis',
                    ],
                    'stock' => [
                        'label' => 'Bestand',
                    ],
                ],
            ],
        ],
    ],

];
