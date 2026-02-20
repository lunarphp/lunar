<?php

return [
    'customer_groups' => [
        'title' => 'Kundengruppen',
        'actions' => [
            'attach' => [
                'label' => 'Kundengruppe hinzufügen',
            ],
        ],
        'form' => [
            'name' => [
                'label' => 'Name',
            ],
            'enabled' => [
                'label' => 'Aktiviert',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
            ],
            'ends_at' => [
                'label' => 'Enddatum',
            ],
            'visible' => [
                'label' => 'Sichtbar',
            ],
            'purchasable' => [
                'label' => 'Käuflich',
            ],
        ],
        'table' => [
            'description' => 'Kundengruppen mit diesem Produkt verknüpfen, um die Verfügbarkeit zu bestimmen.',
            'name' => [
                'label' => 'Name',
            ],
            'enabled' => [
                'label' => 'Aktiviert',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
            ],
            'ends_at' => [
                'label' => 'Enddatum',
            ],
            'visible' => [
                'label' => 'Sichtbar',
            ],
            'purchasable' => [
                'label' => 'Käuflich',
            ],
        ],
    ],
    'channels' => [
        'actions' => [
            'attach' => [
                'label' => 'Weiteren Kanal planen',
            ],
        ],
        'form' => [
            'enabled' => [
                'label' => 'Aktiviert',
                'helper_text_false' => 'Dieser Kanal wird nicht aktiviert, auch wenn ein Startdatum vorhanden ist.',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
                'helper_text' => 'Leer lassen, um ab jedem Datum verfügbar zu sein.',
            ],
            'ends_at' => [
                'label' => 'Enddatum',
                'helper_text' => 'Leer lassen, um unbegrenzt verfügbar zu sein.',
            ],
        ],
        'table' => [
            'description' => 'Bestimmen Sie, welche Kanäle aktiviert sind und planen Sie die Verfügbarkeit.',
            'name' => [
                'label' => 'Name',
            ],
            'enabled' => [
                'label' => 'Aktiviert',
            ],
            'starts_at' => [
                'label' => 'Startdatum',
            ],
            'ends_at' => [
                'label' => 'Enddatum',
            ],
        ],
    ],
    'urls' => [
        'title' => 'URL',
        'title_plural' => 'URLs',
        'actions' => [
            'create' => [
                'label' => 'URL erstellen',
            ],
        ],
        'filters' => [
            'language_id' => [
                'label' => 'Sprache',
            ],
        ],
        'form' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Standard',
            ],
            'language' => [
                'label' => 'Sprache',
            ],
        ],
        'table' => [
            'slug' => [
                'label' => 'Slug',
            ],
            'default' => [
                'label' => 'Standard',
            ],
            'language' => [
                'label' => 'Sprache',
            ],
        ],
    ],
    'customer_group_pricing' => [
        'title' => 'Kundengruppenpreise',
        'title_plural' => 'Kundengruppenpreise',
        'table' => [
            'heading' => 'Kundengruppenpreise',
            'description' => 'Weisen Sie Preise Kundengruppen zu, um den Produktpreis zu bestimmen.',
            'empty_state' => [
                'label' => 'Keine Kundengruppenpreise vorhanden.',
                'description' => 'Erstellen Sie einen Kundengruppenpreis, um zu beginnen.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Kundengruppenpreis hinzufügen',
                    'modal' => [
                        'heading' => 'Kundengruppenpreis erstellen',
                    ],
                ],
            ],
        ],
    ],
    'pricing' => [
        'title' => 'Preisgestaltung',
        'title_plural' => 'Preisgestaltung',
        'tab_name' => 'Preisstaffelungen',
        'table' => [
            'heading' => 'Preisstaffelungen',
            'description' => 'Reduzieren Sie den Preis, wenn ein Kunde in größeren Mengen kauft.',
            'empty_state' => [
                'label' => 'Keine Preisstaffelungen vorhanden.',
            ],
            'actions' => [
                'create' => [
                    'label' => 'Preisstaffelung hinzufügen',
                ],
            ],
            'price' => [
                'label' => 'Preis',
            ],
            'customer_group' => [
                'label' => 'Kundengruppe',
                'placeholder' => 'Alle Kundengruppen',
            ],
            'min_quantity' => [
                'label' => 'Mindestmenge',
            ],
            'currency' => [
                'label' => 'Währung',
            ],
        ],
        'form' => [
            'price' => [
                'label' => 'Preis',
                'helper_text' => 'Der Kaufpreis, vor Rabatten.',
            ],
            'customer_group_id' => [
                'label' => 'Kundengruppe',
                'placeholder' => 'Alle Kundengruppen',
                'helper_text' => 'Wählen Sie die Kundengruppe aus, auf die dieser Preis angewendet werden soll.',
            ],
            'min_quantity' => [
                'label' => 'Mindestmenge',
                'helper_text' => 'Wählen Sie die Mindestmenge aus, für die dieser Preis verfügbar ist.',
                'validation' => [
                    'unique' => 'Kundengruppe und Mindestmenge müssen eindeutig sein.',
                ],
            ],
            'currency_id' => [
                'label' => 'Währung',
                'helper_text' => 'Wählen Sie die Währung für diesen Preis aus.',
            ],
            'compare_price' => [
                'label' => 'Vergleichspreis',
                'helper_text' => 'Der ursprüngliche Preis oder UVP, zum Vergleich mit dem Kaufpreis.',
            ],
            'basePrices' => [
                'title' => 'Preise',
                'form' => [
                    'price' => [
                        'label' => 'Preis',
                        'helper_text' => 'Der Kaufpreis, vor Rabatten.',
                    ],
                    'compare_price' => [
                        'label' => 'Vergleichspreis',
                        'helper_text' => 'Der ursprüngliche Preis oder UVP, zum Vergleich mit dem Kaufpreis.',
                    ],
                ],
                'tooltip' => 'Automatisch basierend auf Wechselkursen generiert.',
            ],
        ],
    ],
    'values' => [
        'title' => 'Werte',
        'form' => [
            'name' => [
                'label' => 'Name',
            ],
        ],
        'table' => [
            'name' => [
                'label' => 'Name',
            ],
            'position' => [
                'label' => 'Position',
            ],
        ],
    ],
];
