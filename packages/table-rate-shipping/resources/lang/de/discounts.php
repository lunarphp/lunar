<?php

return [
    'shipping_discount' => [
        'name' => 'Versandpreis',
        'form' => [
            'methods' => [
                'label' => 'Versandarten',
                'add_label' => 'Regel hinzufügen',
            ],
            'shipping_method_id' => [
                'label' => 'Versandart',
                'placeholder' => 'Beliebige Versandart',
            ],
            'type' => [
                'label' => 'Rabatttyp',
                'options' => [
                    'fixed' => 'Festpreis',
                    'percentage' => 'Prozentualer Rabatt',
                ],
            ],
            'percentage' => [
                'label' => 'Prozentualer Rabatt (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Regeln',
            'rules_description' => 'Jede Regel gilt für eine Versandart oder für alle Versandarten, wenn keine ausgewählt ist. Eine bestimmte Versandart hat Vorrang vor der allgemeinen Regel.',
            'add_rule' => 'Regel hinzufügen',
            'remove_rule' => 'Regel entfernen',
            'no_rules' => 'Noch keine Regeln. Fügen Sie eine hinzu, um die Versandkosten zu ändern.',
            'method' => 'Versandart',
            'method_any' => 'Beliebige Versandart',
            'type' => 'Wirkung',
            'type_fixed' => 'Versandpreis festlegen',
            'type_percentage' => 'Prozentualen Rabatt gewähren',
            'percentage' => 'Prozentualer Rabatt (%)',
            'prices' => 'Versandpreis wird',
            'prices_hint' => 'Der Kunde zahlt diesen Betrag für den Versand. Lassen Sie eine Währung leer, um deren Preis unverändert zu lassen; geben Sie 0 für kostenlosen Versand ein.',
        ],
        'summary_free' => 'Kostenloser Versand',
        'summary_percentage' => ':percentage% Rabatt auf den Versand',
        'summary_from' => 'Versand ab :amount',
    ],
];
