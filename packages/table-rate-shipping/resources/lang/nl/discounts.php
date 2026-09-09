<?php

return [
    'shipping_discount' => [
        'name' => 'Verzendprijs',
        'form' => [
            'methods' => [
                'label' => 'Verzendmethoden',
                'add_label' => 'Regel toevoegen',
            ],
            'shipping_method_id' => [
                'label' => 'Verzendmethode',
                'placeholder' => 'Elke verzendmethode',
            ],
            'type' => [
                'label' => 'Kortingstype',
                'options' => [
                    'fixed' => 'Vaste prijs',
                    'percentage' => 'Procentuele korting',
                ],
            ],
            'percentage' => [
                'label' => 'Procentuele korting (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Regels',
            'rules_description' => 'Elke regel geldt voor één verzendmethode, of voor alle methoden als er geen is gekozen. Een specifieke methode gaat vóór de algemene regel.',
            'add_rule' => 'Regel toevoegen',
            'remove_rule' => 'Regel verwijderen',
            'no_rules' => 'Nog geen regels. Voeg er een toe om de verzendkosten te wijzigen.',
            'method' => 'Verzendmethode',
            'method_any' => 'Elke verzendmethode',
            'type' => 'Effect',
            'type_fixed' => 'Verzendprijs instellen',
            'type_percentage' => 'Een percentage korting geven',
            'percentage' => 'Procentuele korting (%)',
            'prices' => 'Verzendprijs wordt',
            'prices_hint' => 'De klant betaalt dit bedrag voor verzending. Laat een valuta leeg om de prijs ongewijzigd te laten; vul 0 in voor gratis verzending.',
        ],
        'summary_free' => 'Gratis verzending',
        'summary_percentage' => ':percentage% korting op verzending',
        'summary_from' => 'Verzending vanaf :amount',
    ],
];
