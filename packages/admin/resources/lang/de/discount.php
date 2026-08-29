<?php

return [
    'plural_label' => 'Rabatte',
    'label' => 'Rabatt',
    'form' => [
        'conditions' => [
            'heading' => 'Bedingungen',
        ],
        'buy_x_get_y' => [
            'heading' => 'Kaufe X, erhalte Y',
        ],
        'percentage_off' => [
            'heading' => 'Prozentualer Rabatt',
        ],
        'fixed_amount_off' => [
            'heading' => 'Fester Rabattbetrag',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'handle' => [
            'label' => 'Handle',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Enddatum',
        ],
        'priority' => [
            'label' => 'Priorität',
            'helper_text' => 'Rabatte mit höherer Priorität werden zuerst angewendet.',
        ],
        'stop' => [
            'label' => 'Weitere Rabatte nach diesem nicht anwenden',
            'helper_text' => 'When this discount applies, any discount with a lower priority will be skipped. Give discounts different priorities to control the order they apply in.',
        ],
        'coupon' => [
            'label' => 'Gutschein',
            'helper_text' => 'Geben Sie den Gutschein ein, der erforderlich ist, damit der Rabatt angewendet wird. Wenn leer, wird der Rabatt automatisch angewendet.',
        ],
        'max_uses' => [
            'label' => 'Maximale Verwendungen',
            'helper_text' => 'Leer lassen für unbegrenzte Verwendungen.',
        ],
        'max_uses_per_user' => [
            'label' => 'Maximale Verwendungen pro Benutzer',
            'helper_text' => 'Leer lassen für unbegrenzte Verwendungen.',
        ],
        'minimum_cart_amount' => [
            'label' => 'Mindestwarenkorbwert',
        ],
        'min_qty' => [
            'label' => 'Produktmenge',
            'helper_text' => 'Legen Sie fest, wie viele qualifizierende Produkte erforderlich sind, damit der Rabatt angewendet wird.',
        ],
        'reward_qty' => [
            'label' => 'Anzahl der kostenlosen Artikel',
            'helper_text' => 'Wie viele von jedem Artikel rabattiert werden.',
        ],
        'max_reward_qty' => [
            'label' => 'Maximale Belohnungsmenge',
            'helper_text' => 'Die maximale Anzahl der Produkte, die unabhängig von den Kriterien rabattiert werden können.',
        ],
        'automatic_rewards' => [
            'label' => 'Belohnungen automatisch hinzufügen',
            'helper_text' => 'Einschalten, um Belohnungsprodukte hinzuzufügen, wenn sie nicht im Warenkorb vorhanden sind.',
        ],
        'percentage' => [
            'label' => 'Prozentsatz',
        ],
    ],
    'table' => [
        'name' => [
            'label' => 'Name',
        ],
        'status' => [
            'label' => 'Status',
            'active' => [
                'label' => 'Aktiv',
            ],
            'pending' => [
                'label' => 'Ausstehend',
            ],
            'expired' => [
                'label' => 'Abgelaufen',
            ],
            'scheduled' => [
                'label' => 'Geplant',
            ],
        ],
        'type' => [
            'label' => 'Typ',
        ],
        'starts_at' => [
            'label' => 'Startdatum',
        ],
        'ends_at' => [
            'label' => 'Enddatum',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
        'coupon' => [
            'label' => 'Coupon',
        ],
    ],
    'pages' => [
        'availability' => [
            'label' => 'Verfügbarkeit',
        ],
        'edit' => [
            'title' => 'Basic Information',
        ],
        'limitations' => [
            'label' => 'Beschränkungen',
        ],
    ],
    'relationmanagers' => [
        'collections' => [
            'title' => 'Sammlungen',
            'description' => 'Wählen Sie, auf welche Sammlungen dieser Rabatt beschränkt sein soll.',
            'actions' => [
                'attach' => [
                    'label' => 'Sammlung hinzufügen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Beschränkung',
                    ],
                    'exclusion' => [
                        'label' => 'Ausschluss',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beschränkung',
                        ],
                        'exclusion' => [
                            'label' => 'Ausschluss',
                        ],
                    ],
                ],
            ],
        ],
        'customers' => [
            'title' => 'Customers',
            'description' => 'Select which customers this discount should be limited to.',
            'actions' => [
                'attach' => [
                    'label' => 'Attach Customer',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'brands' => [
            'title' => 'Marken',
            'description' => 'Wählen Sie, auf welche Marken dieser Rabatt beschränkt sein soll.',
            'actions' => [
                'attach' => [
                    'label' => 'Marke hinzufügen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Beschränkung',
                    ],
                    'exclusion' => [
                        'label' => 'Ausschluss',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beschränkung',
                        ],
                        'exclusion' => [
                            'label' => 'Ausschluss',
                        ],
                    ],
                ],
            ],
        ],
        'products' => [
            'title' => 'Produkte',
            'description' => 'Wählen Sie, auf welche Produkte dieser Rabatt beschränkt sein soll.',
            'actions' => [
                'attach' => [
                    'label' => 'Produkt hinzufügen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Beschränkung',
                    ],
                    'exclusion' => [
                        'label' => 'Ausschluss',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beschränkung',
                        ],
                        'exclusion' => [
                            'label' => 'Ausschluss',
                        ],
                    ],
                ],
            ],
        ],
        'rewards' => [
            'title' => 'Produktbelohnungen',
            'description' => 'Wählen Sie, welche Produkte rabattiert werden, wenn sie im Warenkorb vorhanden sind und die oben genannten Bedingungen erfüllt sind.',
            'actions' => [
                'attach' => [
                    'label' => 'Produkt hinzufügen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Beschränkung',
                    ],
                    'exclusion' => [
                        'label' => 'Ausschluss',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beschränkung',
                        ],
                        'exclusion' => [
                            'label' => 'Ausschluss',
                        ],
                    ],
                ],
            ],
        ],
        'conditions' => [
            'title' => 'Produktbedingungen',
            'description' => 'Wählen Sie die Produkte aus, die erforderlich sind, damit der Rabatt angewendet wird.',
            'actions' => [
                'attach' => [
                    'label' => 'Produkt hinzufügen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'type' => [
                    'label' => 'Typ',
                    'limitation' => [
                        'label' => 'Beschränkung',
                    ],
                    'exclusion' => [
                        'label' => 'Ausschluss',
                    ],
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Beschränkung',
                        ],
                        'exclusion' => [
                            'label' => 'Ausschluss',
                        ],
                    ],
                ],
            ],
        ],
        'collection_conditions' => [
            'title' => 'Collection Conditions',
            'description' => 'Select the collection conditions required for the discount to apply.',
            'actions' => [
                'attach' => [
                    'label' => 'Add Condition',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
            ],
        ],
        'productvariants' => [
            'title' => 'Produktvarianten',
            'description' => 'Wählen Sie, auf welche Produktvarianten dieser Rabatt beschränkt sein soll.',
            'actions' => [
                'attach' => [
                    'label' => 'Produktvariante hinzufügen',
                ],
            ],
            'table' => [
                'name' => [
                    'label' => 'Name',
                ],
                'sku' => [
                    'label' => 'SKU',
                ],
                'values' => [
                    'label' => 'Option(en)',
                ],
            ],
            'form' => [
                'type' => [
                    'options' => [
                        'limitation' => [
                            'label' => 'Begrenzung',
                        ],
                        'exclusion' => [
                            'label' => 'Ausschluss',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
