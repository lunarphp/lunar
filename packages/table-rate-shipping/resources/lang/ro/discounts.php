<?php

return [
    'shipping_discount' => [
        'name' => 'Prețul livrării',
        'form' => [
            'methods' => [
                'label' => 'Metode de livrare',
                'add_label' => 'Adaugă regulă',
            ],
            'shipping_method_id' => [
                'label' => 'Metodă de livrare',
                'placeholder' => 'Orice metodă de livrare',
            ],
            'type' => [
                'label' => 'Tip reducere',
                'options' => [
                    'fixed' => 'Preț fix',
                    'percentage' => 'Reducere procentuală',
                ],
            ],
            'percentage' => [
                'label' => 'Reducere procentuală (%)',
            ],
        ],
        'panel' => [
            'rules' => 'Reguli',
            'rules_description' => 'Fiecare regulă se aplică unei metode de livrare sau tuturor metodelor când nu este aleasă niciuna. O metodă specifică are prioritate față de regula generală.',
            'add_rule' => 'Adaugă regulă',
            'remove_rule' => 'Elimină regula',
            'no_rules' => 'Nu există reguli încă. Adaugă una pentru a modifica costul livrării.',
            'method' => 'Metodă de livrare',
            'method_any' => 'Orice metodă de livrare',
            'type' => 'Efect',
            'type_fixed' => 'Setează prețul livrării',
            'type_percentage' => 'Aplică o reducere procentuală',
            'percentage' => 'Reducere procentuală (%)',
            'prices' => 'Prețul livrării devine',
            'prices_hint' => 'Clientul plătește această sumă pentru livrare. Lasă o monedă necompletată pentru a-i păstra prețul neschimbat; introdu 0 pentru livrare gratuită.',
        ],
        'summary_free' => 'Livrare gratuită',
        'summary_percentage' => ':percentage% reducere la livrare',
        'summary_from' => 'Livrare de la :amount',
    ],
];
